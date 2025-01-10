<?php

namespace Gayfullin\BaseHelpers;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelEditorJs\Misc\Tools;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class FileHelper
{
    public const ALLOWED_EXTENSIONS = ['PNG', 'JPG', 'JPEG'];

    /**
     * @throws \Exception
     */
    public static function getFileByLink($file_path, bool $get_as_uploaded_file = true): UploadedFile|File|bool
    {
        if ($stream = @fopen($file_path, 'r')) {
            $temp_file = tempnam(sys_get_temp_dir(), 'url-file-');

            $matches = [];

            if (StringHelper::isAllowedBase64StringFile($file_path, $matches)) {
                $temp_file = $temp_file . '.' . data_get($matches, 1, 'png');
            }

            file_put_contents($temp_file, $stream);

            if ($get_as_uploaded_file) {
                try {
                    $path_info = pathinfo($file_path);

                    $file_name = data_get($path_info, 'filename', 'file-name');

                    if (empty($file_name)) {
                        $file_name = 'file-name';
                    }
                } catch (\Exception $exception) {
                    $file_name = 'file-name';
                }

                $file = new UploadedFile($temp_file, $file_name);
            } else {
                $file = new File($temp_file);
            }

            return $file;
        }

        return false;
    }

    public static function getFileHashByLink($file_path): string|bool
    {
        $file = FileHelper::getFileByLink($file_path, true);

        if ($file) {
            $hash = FileHelper::getFileHash($file);

            unlink($file->getRealPath());

            return $hash;
        }

        return false;
    }

    public static function getFileHash($file): string
    {
        return sha1_file($file);
    }

    public static function throwIfHasNoValidExtension(UploadedFile $file): void
    {
        $extension = data_get(FileHelper::explodeToFileNameAndExtension($file), 'extension');

        if (!empty($extension)) {
            if (!FileHelper::extensionIsAllowed($extension)) {
                $context = (string)array_key_first(request()->file(null, []));

//                throw new  ApiException(__('validation.mimetypes', [
//                    'attribute' => $context,
//                    'values' => strtolower(implode(', ', FileHelper::ALLOWED_EXTENSIONS)),
//                ]), $context, 'validator', HttpResponse::HTTP_UNPROCESSABLE_ENTITY,);
            }
        }
    }

    public static function extensionIsAllowed(?string $extension = null): bool
    {
        return !empty($extension) && in_array(strtolower($extension), array_map('strtolower', FileHelper::ALLOWED_EXTENSIONS), false);
    }

    public static function base64FileToUploadedFile(string $base64_file): UploadedFile
    {
        # Get file data base64 string
        $file_data = base64_decode(Arr::last(explode(',', $base64_file)));

        $mime_type = finfo_buffer(finfo_open(), $file_data, FILEINFO_MIME_TYPE);

        if (isset($mime_type) && !empty($mime_type)) {
            $extension = FilePropertiesHelper::guestExtensionByMimeType($mime_type);
        }

        # Create temp file and get its absolute path
        $temp_file = tmpfile();

        $temp_file_path = stream_get_meta_data($temp_file)['uri'];

        if (isset($extension) && !empty($extension)) {
            $temp_file_path = "$temp_file_path.$extension";
        }

        # Save file data in file
        file_put_contents($temp_file_path, $file_data);

        $temp_file_object = new File($temp_file_path);

        $file = new UploadedFile(
            $temp_file_object->getPathname(),
            $temp_file_object->getFilename(),
            $temp_file_object->getMimeType(),
            0,
            true # Mark it as test, since the file isn't from real HTTP POST.
        );

        # Close this file after response is sent.
        # Closing the file will cause to remove it from temp director!
        app()->terminating(function () use ($temp_file) {
            fclose($temp_file);
        });

        # return UploadedFile object
        return $file;
    }

    public static function getTempDir(): string
    {
        $temp_dir = public_path('/storage/temp');

        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }

        return $temp_dir;
    }


    public static function fileSizeUnitsFormat($bytes): string
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public static function isFile($file): bool
    {
        return ($file instanceof \Illuminate\Http\File) || ($file instanceof \Illuminate\Http\UploadedFile);
    }

    public static function guestExtensionByMimeType($mime_type): ?string
    {
        $extension = null;

        if (!empty($mime_type)) {
            $extension = FileHelper::setValidExtension(MimeToExtension::mime2ext($mime_type));

            if (empty($extension)) {
                $extension = FileHelper::setValidExtension(Str::afterLast($mime_type, "/"));
            }
        }

        return $extension;
    }

    private static function setValidExtension($temp_extension): ?string
    {
        if (FileHelper::isValidExtension($temp_extension)) {
            return trim($temp_extension);
        }

        return null;
    }

    public static function isValidExtension($extension): bool
    {
        /**{ Example @see \Symfony\Component\Mime\MimeTypes::REVERSE_MAP}**/
        if (empty($extension) || !is_string($extension)) {
            return false;
        }

        $extension = strtolower(trim($extension));

        return array_key_exists($extension, MimeToExtension::REVERSE_MAP);
    }

    public static function getExtension(\Illuminate\Http\UploadedFile|\Illuminate\Http\File|string|null $file): ?string
    {
        if (empty($file)) {
            return null;
        }

        $extension = null;

        $matches = [];

        try {
            if (FileHelper::isFile($file)) {
                $extension = FileHelper::setValidExtension($file->getExtension());
//            \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['file getExtension' => $extension, 'extension_result' => $file->getExtension()]);

                if (empty($extension)) {
                    $extension = FileHelper::setValidExtension($file->guessExtension());
//                \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['file guessExtension' => $extension, 'extension_result' => $file->guessExtension()]);
                }

                /**It should be at the end**/
                if (empty($extension)) {
                    $file = $file->getRealPath();
                }
            }

            if (is_string($file)) {
//            if (FileHelper::isAllowedBase64StringFile($file_path, $matches)) {
//                $extension = data_get($matches, 2);
//            }

                if (empty($extension)) {
                    $extension = FileHelper::setValidExtension(pathinfo($file, PATHINFO_EXTENSION));
//                \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['PATHINFO_EXTENSION' => $extension, 'extension_result' => pathinfo($file, PATHINFO_EXTENSION)]);
                }

                if (empty($extension)) {
                    $image_size = @getimagesize($file);

                    if ($image_size) {
                        $mime_type = data_get($image_size, 'mime');

                        if (!empty($mime_type)) {
                            $extension = FileHelper::setValidExtension(FileHelper::guestExtensionByMimeType($mime_type));
//                        \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['getimagesize' => $extension]);
                        }
                    }
                }
//            /**new methods*/

                if (empty($extension)) {
                    $stream = @fopen($file, 'r');

                    if (is_resource($stream)) {
                        $meta = @stream_get_meta_data($stream);

                        $mime_type = data_get($meta, 'mediatype');
//                    \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['fopen stream_get_meta_data - stream_get_meta_data ' => $meta]);

                        if (!empty($mime_type)) {
                            $extension = FileHelper::setValidExtension(FileHelper::guestExtensionByMimeType($mime_type));
//                        \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['fopen stream_get_meta_data - extension' => $extension]);
                        }

                        @fclose($stream);
                    }
                }

                if (empty($extension)) {
                    /**This function may only be used against URLs*/
                    $headers = @get_headers($file, 1);

                    if (is_array($headers)) {
                        if (!empty($temp_extension = data_get($headers, 'Content-Type'))) {
                            $extension = FileHelper::setValidExtension($temp_extension);
//                        \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['get_headers' => $extension]);
                        }
                    }
                }
            }
        } catch (\Throwable $exception) {
            $extension = null;
        }

        return $extension;
    }

    private static function setValidMimeType($temp_mime_type): ?string
    {
        if (FileHelper::isValidMimeType($temp_mime_type)) {
            return trim($temp_mime_type);
        }

        return null;
    }

    public static function getMimeType(\Illuminate\Http\UploadedFile|\Illuminate\Http\File|string|null $file): ?string
    {
        $mime_type = null;

        try {
            if (FileHelper::isFile($file)) {
                $mime_type = FileHelper::setValidMimeType($file->getMimeType());
//                \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['file getMimeType' => $mime_type, 'mime_type_result' => $file->getMimeType()]);

                /**It should be at the end**/
                if (empty($mime_type)) {
                    $file = $file->getRealPath();
                }
            }

            if (is_string($file)) {
                if (empty($mime_type) && function_exists("finfo_file")) {
                    $finfo = @finfo_open(FILEINFO_MIME_TYPE);

                    $mime_type = FileHelper::setValidMimeType(finfo_file($finfo, $file));
//                    \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['finfo_file' => $mime_type, 'mime_type_result' => @finfo_file($finfo, $file)]);

                    @finfo_close($finfo);
                }

                if (empty($mime_type) && function_exists("mime_content_type")) {
                    $mime_type = FileHelper::setValidMimeType(@mime_content_type($file));
//                    \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['mime_content_type' => $mime_type, 'mime_type_result' => @mime_content_type($file)]);
                }

                if (empty($mime_type) && !stristr(ini_get("disable_functions"), "shell_exec")) {
                    // http://stackoverflow.com/a/134930/1593459
                    $escape_shell_arg = @escapeshellarg($file);

                    $temp_mime_type = @shell_exec("file -bi " . $escape_shell_arg);

                    $temp_mime_type = trim($temp_mime_type);
                    $temp_mime_type = preg_replace("/ [^ ]*/", "", $temp_mime_type);
                    $temp_mime_type = rtrim($temp_mime_type, ';');
                    $mime_type = FileHelper::setValidMimeType($temp_mime_type);
//                    \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['shell_exec' => $mime_type, 'mime_type_result' => $temp_mime_type]);
                }

                if (empty($mime_type)) {
                    $image_size = @getimagesize($file);

                    if ($image_size) {
                        $temp_mime_type = data_get($image_size, 'mime');
                        $mime_type = FileHelper::setValidMimeType($temp_mime_type);
//                        \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['getimagesize' => $mime_type, 'mime_type_result' => $temp_mime_type]);
                    }
                }
            }

        } catch (\Exception $exception) {
            $mime_type = null;
        }

        return $mime_type;
    }


    public static function isValidMimeType($mime_type): bool
    {
        if (empty($mime_type) || !is_string($mime_type)) {
            return false;
        }

        $mime_type = strtolower(trim($mime_type));

        return array_key_exists($mime_type, MimeToExtension::MAP);
    }

    public static function explodeToFileNameAndExtension(UploadedFile|\Illuminate\Http\File $file, $key = null): array|string|null
    {
        $extension = FileHelper::getExtension($file);
        $mime_type = FileHelper::getMimeType($file);

//        \Illuminate\Support\Facades\Log::debug(__FUNCTION__, ['getting_file_extension' => $extension, 'file_extension_facade' => \Illuminate\Support\Facades\File::extension($file->getRealPath()), 'guessExtension' => $file->guessExtension(), 'guessClientExtension' => $file->guessClientExtension(), 'file_mime_type' => $mime_type, 'get_extension_func' => FileHelper::getExtension($file),]);

        $name = null;

        if (empty($extension) && !empty($mime_type)) {
            $extension = FileHelper::guestExtensionByMimeType($mime_type);
        }

        $full_file_name = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getBasename();

        if (!empty($extension)) {
            $name = str_replace(MimeToExtension::getExtensionsListStartedWithDot(), "", $full_file_name);
        }

        $data = [
            'name' => $name,
            'extension' => $extension,
        ];

//        \Illuminate\Support\Facades\Log::debug(__CLASS__.'::'.__FUNCTION__.' - result', array_merge($data, ['full_file_name' => $full_file_name]));

        if ($key == null) {
            return $data;
        }

        return data_get($data, $key, []);
    }
}