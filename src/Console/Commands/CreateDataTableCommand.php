<?php

namespace SteelAnts\DataTable\Console\Commands;

use Illuminate\Console\Command;

class CreateDataTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-table {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new data-table class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $content = '';
        $content .= $this->generateMethod('mount');
        $this->saveDataTable($this->argument('name'), $content);
    }

    private function getHead($className) {
        return "<?php

        namespace App\Livewire\Components;

        use SteelAnts\DataTable\Livewire\DataTable;

        class $className extends DataTable
        {
        ";
    }

    private function getFoot() {
        return "\n\n}
        ";
    }

    private function generateMethod($name) {
        return "
        public function $name()
        {
            parent::getData();
        }
        ";
    }

    private function saveDataTable($name, $content) {
        if(empty($content)) return;

        $testFilePath = base_path() . '/app/Livewire/Components/'.$name.'.php';

        $fp = fopen($testFilePath , 'w');
        fwrite($fp,  $this->getHead($name).$content.$this->getFoot());
        fclose($fp);

        $this->info("DataTable generated:  /app/Livewire/Components/$name.php");
    }
}
