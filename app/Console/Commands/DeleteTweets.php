<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SaveTweets;

class DeleteTweets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:tweets {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete tweets by conditions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $saveTweets = new SaveTweets();
        $saveTweets->deleteTweets($this->argument('date'));
        $this->info('Delete success!!');
    }
}
