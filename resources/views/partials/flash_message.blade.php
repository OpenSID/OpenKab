@if ($message = Session::get('success'))
    <div id="notifikasi" data-testid="flash-success" class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-check"></i> Sukses!</h4>
        <p>{{ $message }}</p>
    </div>
@elseif($message = Session::get('error'))
    <div id="notifikasi" data-testid="flash-error" class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-meh-o"></i> Gagal!!</h4>
        <p>{{ $message }}</p>
    </div>
@elseif($message = Session::get('warning'))
    <div id="notifikasi" data-testid="flash-warning" class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-exclamation-triangle"></i> Perhatian!</h4>
        <p>{{ $message }}</p>
    </div>
@endif
