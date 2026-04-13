<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function certificate()
    {
        $data = [
            'title' => 'Sertifikat Penghargaan',
            'name' => 'Nama Penerima',
            'description' => 'Diberikan sebagai penghargaan atas prestasi dan kontribusi.',
            'date' => date('d F Y'),
        ];

        $pdf = Pdf::loadView('pdf.certificate', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream('sertifikat.pdf');
    }

    public function announcement()
    {
        $data = [
            'title' => 'Pengumuman Fakultas',
            'subject' => 'Undangan Rapat Akademik',
            'body' => "Dengan hormat, seluruh dosen dan tenaga kependidikan diundang untuk hadir dalam rapat akademik...",
            'date' => date('d F Y'),
        ];

        $pdf = Pdf::loadView('pdf.announcement', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('pengumuman.pdf');
    }
}
