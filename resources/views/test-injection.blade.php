@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4>🛡️ Test Anti-Injection Protection</h4>
                </div>
                <div class="card-body">
                    <form id="testForm" method="POST" action="{{ route('test.submit') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Test SQL Injection:</label>
                            <input type="text" class="form-control" name="sql_test" 
                                   placeholder="Coba: ' OR '1'='1">
                            <small class="text-muted">Coba masukkan: ' OR '1'='1 atau SELECT * FROM users</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test XSS:</label>
                            <input type="text" class="form-control" name="xss_test" 
                                   placeholder="Coba: <script>alert('XSS')</script>">
                            <small class="text-muted">Coba masukkan: &lt;script&gt;alert('XSS')&lt;/script&gt;</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test Email:</label>
                            <input type="email" class="form-control" name="email_test" 
                                   placeholder="test@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test File Upload:</label>
                            <input type="file" class="form-control" name="file_test">
                            <small class="text-muted">Coba upload file .php atau file > 5MB</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Test Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
