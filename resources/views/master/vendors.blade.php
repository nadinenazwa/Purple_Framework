@extends('layouts.master')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">Master Data Vendor</h3>
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">Tambah Vendor</div>
        <div class="card-body">
          <form method="POST" action="{{ url('master/vendors') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input name="name" class="form-control" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary">Tambah Vendor</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Daftar Vendor</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="table-light">
                  <tr><th>Nama</th></tr>
                </thead>
                <tbody>
                  @foreach($vendors as $v)
                    <tr>
                      <td>{{ $v->name }}</td>
                    </tr>
                  @endforeach
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
