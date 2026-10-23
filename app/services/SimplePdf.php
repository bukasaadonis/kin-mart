<?php
namespace App\Services;

final class SimplePdf {
    public static function output(string $title, array $lines, string $filename = 'document.pdf'): never {
        $esc = fn($s) => str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], iconv('UTF-8','Windows-1252//TRANSLIT',(string)$s) ?: (string)$s);
        $content = "BT\n/F1 18 Tf\n50 790 Td\n(".$esc($title).") Tj\n/F1 10 Tf\n0 -28 Td\n";
        $y=0;
        foreach ($lines as $line) {
            $safe=$esc(mb_substr((string)$line,0,110));
            $content .= "0 -15 Td\n($safe) Tj\n";
            $y++;
            if ($y>45) break;
        }
        $content .= "ET";
        $objects=[];
        $objects[]="<< /Type /Catalog /Pages 2 0 R >>";
        $objects[]="<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[]="<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>";
        $objects[]="<< /Length ".strlen($content)." >>\nstream\n$content\nendstream";
        $objects[]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $pdf="%PDF-1.4\n"; $offsets=[0];
        foreach($objects as $i=>$obj){$offsets[]=strlen($pdf);$n=$i+1;$pdf.="$n 0 obj\n$obj\nendobj\n";}
        $xref=strlen($pdf);$pdf.="xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";
        for($i=1;$i<=count($objects);$i++)$pdf.=sprintf('%010d 00000 n ', $offsets[$i])."\n";
        $pdf.="trailer << /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        echo $pdf; exit;
    }
}
