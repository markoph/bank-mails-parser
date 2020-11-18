<?php

namespace Tomaj\BankMailsParser\Parser;

use Tomaj\BankMailsParser\MailContent;

class TatraBankaSimpleMailParser implements ParserInterface
{
    /**
     * @param $content
     * @return bool|MailContent
     */
    public function parse($content)
    {
        $mailContent = new MailContent();

        $cardpayPattern = '/VS=(.*) RES=(.*) AC=(.*) SIGN=(.*)/m';
        $res = preg_match($cardpayPattern, $content, $result);
        if ($res) {
            $mailContent->setVs($result[1]);
            $mailContent->setRes($result[2]);
            $mailContent->setAc($result[3]);
            $mailContent->setSign($result[4]);
            $mailContent->setTransactionDate(time());
            return $mailContent;
        }
        if (!$res) {
            $comfortpayPattern = '/VS=(.*) TRES=(.*) CID=(.*) SIGN=(.*)/m';
            $res = preg_match($comfortpayPattern, $content, $result);
            if ($res) {
                $mailContent->setVs($result[1]);
                $mailContent->setRes($result[2]);
                $mailContent->setCid($result[3]);
                $mailContent->setSign($result[4]);
                $mailContent->setTransactionDate(time());
                return $mailContent;
            }
        }

        if (!$res) {
            $comfortpayHmacPattern = '/AMT=(.*) CURR=(.*) VS=(.*?)( TXN=(.*))? RES=(.*) AC=(.*) TRES=(.*) CID=([0-9]*) (?:CC=(.*) )?TID=([0-9]*) TIMESTAMP=([0-9]*) HMAC=(.*) ECDSA_KEY=(.) ECDSA=(.*)/m';
            $res = preg_match($comfortpayHmacPattern, $content, $result);
            if ($res) {
                $mailContent->setAmount($result[1]);
                $mailContent->setCurrency($result[2]);
                $mailContent->setVs($result[3]);
                $mailContent->setRes($result[6]);
                $mailContent->setAc($result[7]);
                $mailContent->setCid($result[9]);
                $mailContent->setCc($result[10]);
                $mailContent->setTid($result[11]);
                $mailContent->setTransactionDate($result[12]);
                $mailContent->setSign($result[13]);
                if (!empty($result[5])) {
                    $mailContent->setTxn($result[5]);
                }
                return $mailContent;
            }

            $comfortpayHmacErrorPattern = '/AMT=([^\s]+) CURR=([^\s]+) VS=([^\s]+) RES=([^\s]+) (?:TRES=(.*?) )?(?:CC=(.*?) )?(?:TID=([0-9]*?) )?TIMESTAMP=([0-9]*?) HMAC=([^\s]+) ECDSA_KEY=(.) ECDSA=([^\s]+)/m';
            $res = preg_match($comfortpayHmacErrorPattern, $content, $result);
            if ($res) {
                $mailContent->setAmount($result[1]);
                $mailContent->setCurrency($result[2]);
                $mailContent->setVs($result[3]);
                $mailContent->setRes($result[4]);
                $mailContent->setCc($result[6]);
                $mailContent->setTid($result[7]);
                $mailContent->setTransactionDate($result[8]);
                $mailContent->setSign($result[9]);
                return $mailContent;
            }
        }

        if (!$res) {
            $cardpayHmacPattern = '/AMT=(.*) CURR=(.*) VS=(.*) RES=(.*) AC=(.*) TID=([0-9]*) TIMESTAMP=([0-9]*) HMAC=(.*) ECDSA_KEY=1 ECDSA=(.*)/m';
            $res = preg_match($cardpayHmacPattern, $content, $result);
            if ($res) {
                $mailContent->setAmount($result[1]);
                $mailContent->setCurrency($result[2]);
                $mailContent->setVs($result[3]);
                $mailContent->setRes($result[4]);
                $mailContent->setAc($result[5]);
                $mailContent->setTid($result[6]);
                $mailContent->setTransactionDate($result[7]);
                $mailContent->setSign($result[8]);
                return $mailContent;
            }
        }

        return false;
    }
}
