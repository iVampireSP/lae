<?php

namespace App\Console\Commands;

use App\Models\Balance;
use App\Models\Host;
use App\Models\User;
use Illuminate\Console\Command;

class GetUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user {email_or_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取一个用户的信息，交易记录，余额，主机，服务器，工单。';

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
    public function handle(): int
    {
        //
        $email_or_id = $this->argument('email_or_id');

        $user = (new User)->where('email', $email_or_id)->orWhere('id', $email_or_id)->orWhere('name', $email_or_id)->first();

        // $transaction = new Transaction();

        $this->warn('用户基本信息');

        $this->info('用户 ID: '.$user->id);
        $this->info('名称: '.$user->name);
        $this->info('邮箱: '.$user->email);
        $this->info('余额：'.$user->balance.' 元');

        $this->warn('前 10 条充值记录');

        $balances = (new Balance)->where('user_id', $user->id)->whereNotNull('paid_at')->latest()->limit(10)->get();

        // 倒序输出
        foreach (array_reverse($balances->toArray()) as $balance) {
            $this->info('['.$balance['paid_at'].'] 支付方式: '.$balance['payment'].' 金额：'.$balance['amount'].' 元');
        }

        $this->warn('前 10 个主机');

        $hosts = (new Host)->where('user_id', $user->id)->with('module')->latest()->limit(10)->get();

        // 倒序
        foreach (array_reverse($hosts->toArray()) as $host) {
            $this->info('['.$host['module']['name'].']('.$host['price'].' 元) '.$host['name']);
        }

        return 0;
    }
}
