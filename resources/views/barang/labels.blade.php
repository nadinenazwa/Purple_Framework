<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Label Cetak</title>
  <style>
    /* Paper size: 21cm x 16cm (210mm x 160mm) - use zero page margin and place content explicitly */
    @page { size: 210mm 160mm; margin: 0; }
    html, body { margin:0; padding:0; width:210mm; height:160mm; font-family: sans-serif; }
    /* place sheet absolutely at page top-left inside the page margins (left/right 5mm, top/bottom 4mm) */
    .sheet { position: absolute; left:5mm; top:4mm; width:200mm; height:152mm; padding:0; margin:0; }
    /* Absolute positioned cells inside the sheet: exact 40mm x 19mm boxes */
    .cell { position: absolute; width:40mm; height:19mm; box-sizing: border-box; padding:1mm; overflow: hidden; }
    .cell .name { font-size:8px; margin-bottom:1mm; }
    .cell .price { font-size:11px; font-weight:bold; margin-bottom:1mm; }
    .cell .meta { font-size:7px; font-weight:normal; color:#222; }
    table.labels { width:100%; height:100%; border-collapse: collapse; table-layout: fixed; border-spacing:0; font-family: sans-serif; }
    /* 5 columns x 8 rows. Each cell exactly 40mm x 19mm */
    table.labels tr { height:19mm; }
    table.labels td { width:40mm; height:19mm; border: none; padding:0; margin:0; box-sizing: border-box; vertical-align: top; overflow: hidden; }
    /* Prevent Dompdf from splitting the table across pages */
    table.labels, table.labels tr, table.labels td { page-break-inside: avoid; page-break-after: avoid; }
    /* inner wrapper keeps small padding without altering cell box size */
    .cell-inner { padding:1mm; box-sizing: border-box; height:100%; overflow: hidden; }
    .price { font-size:11px; font-weight:bold; line-height:1; }
    .name { font-size:8px; line-height:1; }
    /* Ensure contents stay at the top of the cell */
    .cell-inner { display:flex; flex-direction:column; justify-content:flex-start; }
    .cell-inner > div { margin:0; padding:0; }
  </style>
</head>
<body>
  @php $grid = $grid ?? []; @endphp
  <div class="sheet">
    @foreach($grid as $rIndex => $row)
      @foreach($row as $cIndex => $cell)
        <div class="cell" style="left: {{ $cIndex * 40 }}mm; top: {{ $rIndex * 19 }}mm;">
          @if($cell)
            <div class="name">{{ $cell->nama }}</div>
            <div class="price">Rp {{ number_format($cell->harga,0,',','.') }}</div>
            <div class="meta">{{ $cell->id_barang }}</div>
          @endif
        </div>
      @endforeach
    @endforeach
  </div>

  <!-- Dependencies (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(function(){
      // Initialize Select2 for the second select
      $('#select2Select').select2({
        placeholder: '-- Pilih kota --',
        width: '100%'
      });

      // Tambahkan option ke kedua select saat tombol diklik
      $('#btnTambah').on('click', function(e){
        e.preventDefault();
        var kotaInputEl = document.getElementById('kotaInput');
        // HTML5 validation: check and report
        if (!kotaInputEl.checkValidity()) {
          kotaInputEl.reportValidity();
          return;
        }
        var kota = $('#kotaInput').val().trim();

        // create new option element
        var $opt1 = $('<option>').val(kota).text(kota);
        var $opt2 = $('<option>').val(kota).text(kota);

        // append to normal select
        $('#normalSelect').append($opt1);

        // append to select2 and notify Select2 to update
        $('#select2Select').append($opt2).trigger('change');

        // clear input
        $('#kotaInput').val('');
      });

      // Saat user memilih kota di salah satu select, tampilkan ke #kotaTerpilih
      $('#normalSelect, #select2Select').on('change', function(){
        // use $(this).val() per requirement
        var val = $(this).val();
        if(!val) val = '-';
        $('#kotaTerpilih').text(val);
      });
    });
  </script>

  <!-- POS demo removed for label printing -->
  <!-- The interactive POS demo was removed from this print template to keep labels clean. -->
</body>
</html>
