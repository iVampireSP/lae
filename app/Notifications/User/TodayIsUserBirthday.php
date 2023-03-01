<?php

namespace App\Notifications\User;

use App\Notifications\Channels\WebChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TodayIsUserBirthday extends Notification implements ShouldQueue
{
    use Queueable;

    private string $subject = '生日快乐';

    private string $greeting = '生日快乐🎂';

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return [WebChannel::class, 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        $email = (new MailMessage)
            ->subject($this->subject)
            ->greeting($this->greeting);

        $lyrics = $this->lyrics();
        foreach ($lyrics as $lyric) {
            $email->line($lyric);
        }

        $email->line($this->suffixText());

        return $email;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(): array
    {
        return [
            'title' => $this->greeting,
            'message' => $this->lyrics(true).$this->suffixText(),
        ];
    }

    protected function lyrics(bool $to_string = false): array|string
    {
        $lyrics = [
            [
                'Happy Birthday',
                '好想把我的心意传达给你。',
            ],
            [
                '今天祝你生日快乐！',
                '这是第几次呢 假装忘记了(实际上)。',
                '心里很清楚 只是在装傻。',
            ],
            [
                '今天祝你生日快乐！',
                '蛋糕上的蜡烛要立几根好呢。连备用的都一起买好了！',
            ],
            [
                'Happy Birthday!',
                '人与人的相遇真是不可思议。',
            ],
            [
                '你知道吗？',
                '你对我而言很重要(一定要说出来)',
                '会不会太迟了呢？我喜欢你，还会更喜欢你。',
            ],
            [
                '即使以心传心但也一定有着极限的。所以要好好地说出来。',
            ],
        ];

        $random_lyrics = $lyrics[array_rand($lyrics)];

        if ($to_string) {
            $lyric_string = '';
            foreach ($random_lyrics as $lyric) {
                $lyric_string .= $lyric.PHP_EOL.PHP_EOL;
            }

            return $lyric_string;
        }

        return $random_lyrics;
    }

    public function suffixText(): string
    {
        $today = now()->format('Y-m-d');

        return <<<EOF
在这特别的日子里，我们将 《ハピハピ♪バースデイソング ～チノ》 中的歌词献给特别的你。


{$today}, 生日快乐！
EOF;
    }
}
