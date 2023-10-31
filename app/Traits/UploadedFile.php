<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Ramsey\Uuid\Uuid;
use RuntimeException;

trait UploadedFile
{
    private $basePath = 'app/public/';

    protected $pathFolder = 'uploads';

    protected function uploadFile(Request $request, $name)
    {
        $file = $request->file($name);
        $storagePathFolder = storage_path($this->basePath.$this->pathFolder);
        if (! File::isDirectory($storagePathFolder)) {
            \Log::error('buat folder dulu '.$storagePathFolder);
            File::makeDirectory($storagePathFolder, 0755, true, true);
        }

        if (empty($file)) {
            throw new RuntimeException('file '.$name.' is required');
        }

        if (! $file->isValid()) {
            throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
        }

        $extensionPhoto = File::guessExtension(request()->file($name));
        $newName = $name.'_'.Uuid::uuid4()->toString().'.'.$extensionPhoto;
        if ($file->move($storagePathFolder, $newName)) {
            return $this->pathFolder.'/'.$newName;
        }

        return false;
    }
}
