<?php
namespace App\Helpers;
use League\Csv\Reader;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemReader;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;

class FileSystemFetcher
{
    /**
     * @var Filesystem Contains the flysystem instance so we can easily access it.
     */
    private Filesystem $filesystem;

    /**
     * @var array Array of paths to the files that we found on our search
     */
    private array $foundFiles;

    /**
     * @var string The directory where we are looking for files
     */
    private string $directory;

    /**
     * @var array An array containing all the files data
     */
    private array $records;

    public function __construct($dir)
    {
        $this->directory = dirname($_SERVER['PHP_SELF']) . $dir;
        /** setup flysystem for easy file management **/
        $adapter = new LocalFilesystemAdapter(
            $this->directory
        );
        $this->filesystem = new Filesystem($adapter);
    }

    /**
     * Return all files in the parser_test directory
     * Filter out only useful ones since the directory also contains .DS_Store files
     */
    private function getFilesInDirectory($ext = ".log") {
        $this->foundFiles = $this->filesystem->listContents('', FilesystemReader::LIST_DEEP)
            ->filter(fn(StorageAttributes $attributes) => $attributes->isFile() && str_ends_with($attributes->path(), $ext))
            ->map(fn(StorageAttributes $attributes) => realpath($this->directory.'/'.$attributes->path()))
            ->toArray();
    }

    private function readFiles() {
        $files = [];
        foreach($this->foundFiles as $logFile) {
            $csv = Reader::createFromPath($logFile, 'r');
            $csv->setHeaderOffset(0);
            $results =  $csv->getRecords($csv->getHeader());
            foreach ($results as $row) {
                array_push($files, $row);
            }
        }
        $this->records = $files;
    }

    /**
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    private function generateExportFile() {
        $exportFile = new ExportFile($this->records);
        $csvData = $exportFile->exportAsCsv();
        $outputFile = "output.csv";
        try{
            if($this->filesystem->fileExists($outputFile)) {
                $this->filesystem->delete($outputFile);
            }
            $this->filesystem->write($outputFile, $csvData);
        } catch(UnableToDeleteFile $e) {
            throw new \Exception("Unable to delete output.csv", 0 , $e);
        } catch(UnableToWriteFile  $e) {
            throw new \Exception("Unable to write to output.csv", 0 , $e);
        } catch(\Exception $e) {
            throw new \Exception("Unknown problem when trying to write output.csv", 0 ,$e);
        }

    }

    public function exec() {
        $this->getFilesInDirectory();
        $this->readFiles();
        $this->generateExportFile();
    }
}