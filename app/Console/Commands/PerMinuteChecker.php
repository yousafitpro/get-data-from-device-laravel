<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PHPUnit\Util\Test;

class PerMinuteChecker extends Command
{    /**
 * The name and signature of the console command.
 *
 * @var string
 */
    protected $signature = 'checker:everyminute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will run after every minute';

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
     * @return int
     */
    public function handle()
    {
        $test=new test();
        $test->save();
        $this->info('Successfully checked the every minute status');
        return 0;
    }
}
