<?php

namespace App\Repositories;

class Bech32
{
    const CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

    public static function decode($bech)
    {
        $bech = strtolower($bech);
        $pos = strrpos($bech, '1');
        if ($pos === false) {
            throw new \InvalidArgumentException('Missing separator');
        }

        $hrp = substr($bech, 0, $pos);
        $data = substr($bech, $pos + 1);

        $decoded = [];
        foreach (str_split($data) as $char) {
            $index = strpos(self::CHARSET, $char);
            if ($index === false) {
                throw new \InvalidArgumentException('Invalid character in Bech32 string');
            }
            $decoded[] = $index;
        }

        return [$hrp, $decoded];
    }

    public static function encode($hrp, $data)
    {
        $combined = array_merge(self::hrpExpand($hrp), $data, [0, 0, 0, 0, 0, 0]);
        $polymod = self::polymod($combined) ^ 1;
        $enc = $hrp . '1';
        foreach ($data as $value) {
            $enc .= self::CHARSET[$value];
        }
        for ($i = 0; $i < 6; ++$i) {
            $enc .= self::CHARSET[($polymod >> 5 * (5 - $i)) & 31];
        }

        return $enc;
    }

    public static function convertBits(array $data, $fromBits, $toBits, $pad = true)
    {
        $acc = 0;
        $bits = 0;
        $ret = [];
        $maxv = (1 << $toBits) - 1;
        foreach ($data as $value) {
            if ($value < 0 || $value >> $fromBits) {
                throw new \InvalidArgumentException('Invalid data');
            }
            $acc = ($acc << $fromBits) | $value;
            $bits += $fromBits;
            while ($bits >= $toBits) {
                $bits -= $toBits;
                $ret[] = ($acc >> $bits) & $maxv;
            }
        }
        if ($pad && $bits > 0) {
            $ret[] = ($acc << ($toBits - $bits)) & $maxv;
        } elseif ($bits >= $fromBits || (($acc << ($toBits - $bits)) & $maxv)) {
            throw new \InvalidArgumentException('Invalid data');
        }
        return $ret;
    }

    private static function hrpExpand($hrp)
    {
        $ret = [];
        foreach (str_split($hrp) as $c) {
            $ret[] = ord($c) >> 5;
        }
        $ret[] = 0;
        foreach (str_split($hrp) as $c) {
            $ret[] = ord($c) & 31;
        }
        return $ret;
    }

    private static function polymod($values)
    {
        $gen = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        $chk = 1;
        foreach ($values as $p) {
            $b = $chk >> 25;
            $chk = (($chk & 0x1ffffff) << 5) ^ $p;
            for ($i = 0; $i < 5; ++$i) {
                if (($b >> $i) & 1) {
                    $chk ^= $gen[$i];
                }
            }
        }
        return $chk;
    }
}
