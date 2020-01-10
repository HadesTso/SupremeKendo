<?php

namespace App\Console;

use App\Models\Server;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*$schedule->call(function () {
            $serverModel = new Server();

            $server = $serverModel->where(['server_status' => 0])->get();

            foreach ()

        })->everyMinutes();*/
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    #!/bin/bash
    /*step=1 #间隔的秒数

    for (( i = 0; i < 60; i=(i+step) )); do
        /usr/local/php/bin/php /home/www/blog/artisan schedule:run
        sleep $step
    done

    exit 0*/
}
