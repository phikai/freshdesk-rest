<?php

namespace Freshdesk\Config;


class Composer
{
    /**
     * postUpdate composer hook to set Ticket::CC_EMAIL constant
     * @param $event
     */
    public static function postUpdate($event)
    {
        $io = $event->getIO();
        do
        {
            $r = $io->ask(
                'Set cc_emails to use in Tickets (when creating new tickets)? [y/N]',
                'N'
            );
            $path = realpath(
                __DIR__.'../Model/Ticket.php'
            );
            switch ($r)
            {
                case 'N':
                case 'n':
                    $r = null;
                    break;
                case 'Y':
                case 'y':
                    $contents = file_get_contents($path);
                    $cc = $io->ask('Value for CC_EMAILS', '');
                    $cc = preg_replace(
                        '/(?<!\\\\)\'/',
                        '\\\'',
                        $cc
                    );
                    file_put_contents(
                        $path,
                        str_replace(
                            '<your cc_email here>',
                            $cc,
                            $contents
                        )
                    );
                    break;
                default:
                    $io->overwrite(
                        $r.' is not a valid option, please enter y or n'
                    );
            }
        } while ($r !== null);
    }
}
