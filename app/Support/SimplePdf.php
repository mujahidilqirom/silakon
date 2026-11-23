<?php
namespace App\Support;

final class SimplePdf
{
    public static function make(array $lines): string
    {
        $wrapped=[];
        foreach ($lines as $raw) {
            $line=preg_replace('/\s+/',' ',(string)$raw);
            if ($line==='') { $wrapped[]=''; continue; }
            while (mb_strlen($line)>100) { $wrapped[]=mb_substr($line,0,100); $line=mb_substr($line,100); }
            $wrapped[]=$line;
        }
        $pages=array_chunk($wrapped,48);$objects=['<< /Type /Catalog /Pages 2 0 R >>'];$kids=[];$n=3;
        foreach($pages as $page){$kids[]=$n.' 0 R';$n+=3;}
        $objects[]='<< /Type /Pages /Kids ['.implode(' ',$kids).'] /Count '.count($kids).' >>';
        foreach($pages as $page){$pageNo=count($objects)+1;$contentNo=$pageNo+1;$fontNo=$pageNo+2;$objects[]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.$fontNo.' 0 R >> >> /Contents '.$contentNo.' 0 R >>';$s="BT /F1 9 Tf 40 810 Td 12 TL\n";foreach($page as $line){$line=str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$line);$s.='('.$line.') Tj T*\n';}$s.='ET';$objects[]='<< /Length '.strlen($s).' >>\nstream\n'.$s.'\nendstream';$objects[]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';}
        $pdf="%PDF-1.4\n";$off=[0];foreach($objects as $i=>$o){$off[]=strlen($pdf);$pdf.=($i+1)." 0 obj\n".$o."\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";for($i=1;$i<=count($objects);$i++)$pdf.=sprintf("%010d 00000 n \n",$off[$i]);return $pdf."trailer\n<< /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }
}
