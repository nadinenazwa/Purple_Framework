@extends('layouts.master')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Data Customer</h3>
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm">
          <thead class="thead-light">
            <tr>
              <th>Foto</th>
              <th>Nama</th>
              <th>Alamat</th>
              <th class="text-center">Wilayah</th>
              <th class="text-center">Kodepos</th>
            </tr>
          </thead>
          <tbody>
            @foreach($customers as $c)
              <tr>
                <td style="width:80px">
                  @if(!empty($c->photo_blob))
                    <img src="data:image/png;base64,{{ base64_encode($c->photo_blob) }}" alt="foto" class="rounded" style="width:60px;height:60px;object-fit:cover" />
                  @elseif(!empty($c->photo_path))
                    <img src="{{ asset($c->photo_path) }}" alt="foto" class="rounded" style="width:60px;height:60px;object-fit:cover" />
                  @else
                    <div class="bg-light" style="width:60px;height:60px;border-radius:6px"></div>
                  @endif
                </td>
                <td>
                  <strong class="text-secondary">{{ $c->name ?? $c->nama ?? '-' }}</strong>
                </td>
                <td class="align-middle">{{ $c->alamat ?? '-' }}</td>
                <td class="text-center text-muted small align-middle">
                  @if(!empty($c->province_name) || !empty($c->regency_name))
                    {{ strtoupper($c->province_name ?? '') }}
                    <br>
                    {{ strtoupper($c->regency_name ?? '') }}
                  @else
                    -
                  @endif
                </td>
                <td class="text-center align-middle">{{ $c->kodepos ?? $c->postal_code ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
