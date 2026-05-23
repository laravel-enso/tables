<?php

namespace LaravelEnso\Tables\Tests\units\Notifications;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\Notifications\ExportDone;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportDoneTest extends TestCase
{
    #[Test]
    public function does_not_translate_the_filename()
    {
        App::make('translator')->addJsonPath(__DIR__.'/lang');
        App::setLocale('lang');

        $notification = new ExportDone('/tmp/report.xlsx', 'report.xlsx', 10);
        $notifiable = new class {
            public string $name = 'Tester';
        };

        $mail = $notification->toMail($notifiable);

        $this->assertSame('report.xlsx', $mail->viewData['filename']);
    }
}
