<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Label Cetak</title>
  <style>
    /* A4 portrait, no page margins */
    @page { size: A4 portrait; margin: 0; }
    html, body { margin:0; padding:0; width:210mm; height:297mm; font-family: sans-serif; }
    /* place sheet 1mm from left and 1mm from top (reduced top margin) */
    .sheet { position: absolute; left: 1mm; top: 1mm; width:209mm; height:296mm; padding:0; margin:0; }
    .cell { position: absolute; width:38mm; height:18mm; box-sizing: border-box; padding:1mm; overflow: hidden; }
    .cell .name { font-size:8px; margin-bottom:1mm; }
    .cell .price { font-size:11px; font-weight:bold; margin-bottom:1mm; }
    /* cell size increased to allow larger font; horizontal gap increased by 2mm */
    .cell .meta { font-size:9px; font-weight:normal; color:#222; }
    .cell-inner { padding:1mm; box-sizing: border-box; height:100%; overflow: hidden; }
    .price { font-size:11px; font-weight:bold; line-height:1; }
    .name { font-size:8px; line-height:1; }
    .cell-inner { display:flex; flex-direction:column; justify-content:flex-start; }
    .cell-inner > div { margin:0; padding:0; }
    .cell img {
      height: 4mm;
      width: auto;
      max-width: 90%;
      display: block;
      margin: 0 0 0.2mm 0;
      object-fit: contain;
    }
  </style>
</head>
<body>
  @php $grid = $grid ?? []; @endphp
  <div class="sheet">
    @foreach($grid as $rIndex => $row)
      @foreach($row as $cIndex => $cell)
        {{-- compute offsets:
             base horizontal increment = 41mm (38mm cell + 3mm gap)
             add extra 2mm space to the right starting from column 3 (cIndex >= 2)
             add additional 3mm right-shift starting from column 4 (cIndex >= 3)
             move the first row down by 4mm (so it prints lower on the page)
        --}}
        @php
          $baseLeft = $cIndex * 41;
          $extraRight = 0;
          if ($cIndex >= 2) $extraRight += 2; // mm for columns 3+
          if ($cIndex >= 3) $extraRight += 3; // additional mm for columns 4+
          // extra spacing specifically before column 5 (cIndex >= 4)
          if ($cIndex >= 4) $extraRight += 8; // push column 5+ further right (increased)
          $leftPos = $baseLeft + $extraRight;

          // increase vertical spacing between rows by 2mm (was 21 -> now 23)
          $baseTop = $rIndex * 23;
          $topPos = $baseTop;
        @endphp
        <div class="cell" style="left: {{ $leftPos }}mm; top: {{ $topPos }}mm;">
          @if($cell)
            <div class="name">{{ $cell->nama }}</div>
            <div class="price">Rp {{ number_format($cell->harga,0,',','.') }}</div>
            <div class="meta">
              @if(!empty($cell->barcode))
                <div style="margin-bottom:1mm;">
                  <img src="{{ $cell->barcode }}" alt="barcode" />
                </div>
              @endif
              <div>{{ $cell->id_barang }}</div>
            </div>
          @endif
        </div>
      @endforeach
    @endforeach
  </div>
</body>
</html>
