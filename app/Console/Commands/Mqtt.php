<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT as MqttClient;

class Mqtt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        declare(ticks=1);
        pcntl_signal(SIGINT, function () {
            $this->info('Stopping...');
            // 关闭子进程
            posix_kill($pid, SIGKILL);
            exit;
        });

        //  MQTT::publish('some/topic', 'Hello World!');

        $tasks = [
            'publish' => function () {
                $this->info('Publishing...');

                MqttClient::publish('some/topic', 'Hello World!');

                sleep(1);


                return true;
            },
            'subscribe' => function () {
                $this->info('Subscribing...');

                $mqtt = MqttClient::connection();
                $mqtt->subscribe('some/topic', function (string $topic, string $message) {
                    echo sprintf('Received QoS level 1 message on topic [%s]: %s', $topic, $message) . PHP_EOL;
                }, 1);
                $mqtt->loop();

                return true;
            },
        ];


        foreach ($tasks as $key => $task) {
            $this->info('Starting task: ' . $key);
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('could not fork');
            } else if ($pid) {
                // we are the parent
                // pcntl_wait($status); //Protect against Zombie children


                // while (pcntl_waitpid(0, $status) != -1) {
                //     $status = pcntl_wexitstatus($status);
                //     echo "Child $status completed\n";
                // }
            } else {
                // we are the child
                while (true) {
                    if ($task() === false) {
                        break;
                    }
                }
                exit(0);
            }
        }


        return Command::SUCCESS;

        // // 开启新的进程，处理 MQTT 消息
        // $running = 0;     // 记录正在运行的子进程数
        // for ($i = 0; $i < $task_num; $i++) {
        //     $pid = pcntl_fork();
        //     if ($pid == -1) {
        //         die('could not fork');
        //     } else if ($pid) {
        //         $running++; // 进程数+1
        //         if ($running >= $max_process) { // 子进程开启数量达到上限
        //             pcntl_wait($status);        // 等待有子进程退出
        //             $running--;                 // 有子进程退出，进程数-1
        //         }
        //     } else {
        //         // 子进程
        //         echo "子进程开始" . PHP_EOL;
        //         while (true) {
        //             MqttClient::publish('some/topic', 'Hello World!');
        //             // sleep(0.5);
        //         }
        //     }
        // }

        // while ($running) {
        //     pcntl_wait($status);
        //     $running--;
        // }


    }
}
