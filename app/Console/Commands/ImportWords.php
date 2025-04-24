<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dictionary;

class ImportWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-words';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import words from: https://github.com/dwyl/english-words/blob/master/words_dictionary.json to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Importing words...');

        $url = 'https://raw.githubusercontent.com/dwyl/english-words/master/words_dictionary.json';
        $json = file_get_contents($url);
        $words = json_decode($json, true);

        foreach ($words as $word => $number) {
            Dictionary::updateOrCreate(
                ['word' => $word],
                [
                    'definition' => null,
                    'lang' => 'en',
                ]
            );
        }

        $this->info('Words imported successfully!');
    }
}
