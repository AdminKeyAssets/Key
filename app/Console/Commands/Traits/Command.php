<?php

namespace App\Console\Commands\Traits;

trait Command
{
    /**
     * Add Title Message In Command
     *
     * @param $text
     */
    public function titleMessage($text)
    {
        $this->info('========================================== ' . strtoupper($text) . ' ===============================================');
    }

    /**
     * Add Info Message In Command
     *
     * @param $text
     */
    public function infoMessage($text)
    {
        $this->info('[*] ' . $text);
    }

    /**
     * Add Error Message In Command
     *
     * @param $text
     */
    public function errorMessage($text)
    {
        $this->error('[*] ' . $text);
    }

    /**
     * Add Waring Message In Command
     *
     * @param $text
     */
    public function warnMessage($text)
    {
        $this->warn('[*] ' . $text);
    }
}
