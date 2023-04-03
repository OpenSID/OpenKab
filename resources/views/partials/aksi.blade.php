<div class="btn-group blocks" id="exampleToolbar" role="group">
    @if( isset( $edit ) )
        <a href="{!! empty( $edit ) ? 'javascript:void(0)' : $edit !!}" class="{!! empty( $edit ) ? 'disabled' : '' !!}" title="Ubah" data-button="edit">
            <button type="button" class="mr-1 btn btn-success btn-sm" style="width: 40px;"><i class="fa fa-edit" aria-hidden="true"></i></button>
        </a>
    @endif
    @if( isset( $delete ) )
        <a href="javascript:void(0)" class="{!! empty( $delete ) ? 'disabled' : '' !!}" title="Hapus" data-href="{!! empty( $delete ) ? 'javascript:void(0)' : $delete !!}" id="deleteModal">
            <button type="button" class="mr-1 btn btn-icon btn-danger btn-sm" style="width: 40px"><i class="fa fa-trash" aria-hidden="true"></i></button>
        </a>
    @endif
    @if( isset( $active ) )
        <a href="{!! empty( $active ) ? 'javascript:void(0)' : $active !!}" class="{!! empty( $active ) ? 'disabled' : '' !!}" title="Aktifkan" data-button="aktifkan">
            <button type="button" class="mr-1 btn bg-navy btn-sm" style="width: 40px;"><i class="fa fa-lock" aria-hidden="true"></i></button>
        </a>
    @endif
    @if( isset( $deactive ) )
        <a href="{!! empty( $deactive ) ? 'javascript:void(0)' : $deactive !!}" class="{!! empty( $deactive ) ? 'disabled' : '' !!}" title="Non-aktifkan" data-button="non-aktifkan">
            <button type="button" class="mr-1 btn bg-navy btn-sm" style="width: 40px;"><i class="fa fa-unlock" aria-hidden="true"></i></button>
        </a>
    @endif
</div>