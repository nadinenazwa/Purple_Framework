@extends('layouts.master')

@section('content')
<div class="container">
  <h3>Vendor Dashboard - Pesanan Lunas</h3>
  @if($orders->isEmpty())
    <div class="alert alert-info">Tidak ada pesanan berstatus Lunas.</div>
  @else
    <div class="table-responsive">
      <table class="table table-striped">
        <thead><tr><th>ID</th><th>Order ID</th><th>Total</th><th>Status</th><th>Vendor</th></tr></thead>
        <tbody>
          @foreach($orders as $o)
            <tr>
              <td>{{ $o->id ?? $o->id_penjualan ?? '-' }}</td>
              <td>{{ $o->order_id ?? '-' }}</td>
              <td>{{ number_format($o->total ?? 0,0,',','.') }}</td>
              <td>{{ $o->status_bayar ?? '-' }}</td>
              <td>{{ $o->vendor_id ?? '-' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
