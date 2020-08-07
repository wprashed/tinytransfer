<?php
namespace system\helpers;

class Fs
{

    /**
     * Create a new directory in the same directory as current file(\_\_DIR\_\_)
     */
    public function createFolder($dirname)
    {
        if (!is_dir($dirname)) {
            mkdir($dirname);
        }
    }

    /**
     * Rename a directory
     */
    public function renameFolder($dirname, $newdirname)
    {
        if (!is_dir($dirname)) {
            echo "$dirname not found in " . dirname($dirname) . ".";
            exit();
        }
        // rename file
        rename($dirname, $newdirname);
    }

    /**
     * Delete a directory
     */
    public function deleteFolder($dirname)
    {
        if (!is_dir($dirname)) {
            echo "$dirname not found in " . dirname($dirname) . ".";
            exit();
        }
        // delete folder
        rmdir($dirname);
    }

    /**
     * List all files and folders in directory
     */
    public function listDir($dirname = null, $pattern = null)
    {
        if ($dirname == null || $dirname == "") {
            $dirname = __DIR__;
        }
        // list dir content
        $files = glob($dirname . "*$pattern*");
        $filenames = [];
        foreach ($files as $file) {
            $file = pathinfo($file);
            $filename = $file['filename'];
            if (isset($file['extension'])) {
                $extension = $file['extension'];
                array_push($filenames, "$filename.$extension");
            } else {
                array_push($filenames, "$filename");
            }
        }

        return $filenames;
    }

    /**
     * Search Dirs
     *
     * @param string $path
     * @param array $files
     * @return array
     *
     */
    private static function searchDir($path, &$files)
    {
        if (is_dir($path)) {
            $opendir = opendir($path);
            while ($file = readdir($opendir)) {
                if ($file != '.' && $file != '..') {
                    self::searchDir($path . DS . $file, $files);
                }
            }
            closedir($opendir);
            $files[] = $path;
        }
    }

    /**
     * Get Dirs
     *
     * @param string $dir
     * @return array
     *
     */
    public static function listDirs($dir)
    {
        $files = [];
        self::searchDir($dir, $files);
        return $files;
    }



    /**
     * Create a new file in the base directory
     */
    public function createFile($dirname, $filename)
    {
        if (!is_dir($dirname)) {
            $this->createFolder($dirname);
        }
        if (file_exists($dirname . "/" . $filename)) {
            touch($dirname . "/" . time() . "." . $filename);
            return;
        }
        touch($dirname . "/" . $filename);
    }

    /**
     * Write content to a file in the base directory
     */
    public function writeFile($dirname, $filename, $content)
    {
        // ensure that file exists
        if (!file_exists($dirname . "/" . $filename)) {
            $this->createFile($dirname, $filename);
        }
        // write to file
        file_put_contents($dirname . "/" . $filename, $content);
    }

    /**
     * Read the content of a file in the base directory
     */
    public function readFile($dirname, $filename)
    {
        if (!file_exists($dirname . "/" . $filename)) {
            echo "$filename not found in $dirname. Change the base directory if you're sure the file exists.";
            exit();
        }
        // read file contents
        return file_get_contents($dirname . "/" . $filename);
    }

    /**
     * Rename a file in the base directory
     */
    public function renameFile($dirname, $filename, $newfilename)
    {
        if (!file_exists($dirname . "/" . $filename)) {
            echo "$filename not found in $dirname. Change the base directory if you're sure the file exists.";
            exit();
        }
        // rename file
        rename($dirname . "/" . $filename, $dirname . "/" . $newfilename);
    }

    /**
     * Add to the content of a file in the base directory
     */
    public function appendFile($dirname, $filename, $content)
    {
        if (!file_exists($dirname . "/" . $filename)) {
            echo "$filename not found in $dirname. Change the base directory if you're sure the file exists.";
            exit();
        }
        // append data to file
        // read file
        $fileContent = $this->readFile($filename);
        // write to file
        $data = $fileContent . "\n" . $content;
        $this->writeFile($filename, $data);
    }

    /**
     * Delete a file in the base directory
     */
    public function deleteFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        } else {
            unlink($filename);
        }
    }

    /**
     * Copy and paste a file from the base directory
     */
    public function copyFile($dirname, $filename, $to, $rename = true)
    {
        if (!file_exists($dirname . "/" . $filename)) {
            echo "$filename not found in $dirname. Change the base directory if you're sure the file exists.";
            exit();
        }
        $newfilename = $filename;
        if (file_exists($dirname . "/" . $filename) && $rename == true) {
            $newfilename = "(" . time() . ")" . $filename;
        }
        try {
            copy($dirname . "/" . $filename, $to . "/" . $newfilename);
        } catch (\Throwable $err) {
            throw "Unable to copy file: " . $err;
        }
    }

    /**
     * Move a file from the base directory
     */
    public function moveFile($dirname, $filename, $to)
    {
        if (!file_exists($dirname . "/" . $filename)) {
            echo "$filename not found in $dirname. Change the base directory if you're sure the file exists.";
            exit();
        }
        move_uploaded_file($dirname . "/" . $filename, $to);
    }
}
