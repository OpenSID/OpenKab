<div class="btn-group blocks" id="exampleToolbar" role="group">
    @if( isset( $edit_url ) )
        <a href="{!! empty( $edit_url ) ? 'javascript:void(0)' : $edit_url !!}" class="{!! empty( $edit_url ) ? 'disabled' : '' !!}" title="Ubah" data-button="edit">
            <button type="button" class="mr-1 btn btn-success btn-sm" style="width: 40px;"><i class="fa fa-edit" aria-hidden="true"></i></button>
        </a>
    @endif
    @if( isset( $delete_url ) )
        <a href="javascript:void(0)" class="{!! empty( $delete_url ) ? 'disabled' : '' !!}" title="Hapus" data-href="{!! empty( $delete_url ) ? 'javascript:void(0)' : $delete_url !!}" data-button="delete" id="deleteModal">
            <button type="button" class="mr-1 btn btn-icon btn-danger btn-sm" style="width: 40px"><i class="fa fa-trash" aria-hidden="true"></i></button>
        </a>
    @endif
    @if( isset( $active_url ) )
        <a href="javascript:void(0)" class="{!! empty( $active_url ) ? 'disabled' : '' !!}" title="Aktif" data-href="{!! empty( $active_url ) ? 'javascript:void(0)' : $active_url !!}" data-button="delete" id="activeModal">
            <button type="button" class="mr-1 btn btn-icon btn-info btn-sm" style="width: 40px; background-color: #1FF43E; border-color: #1FF43E;"><i class="fa fa-check" aria-hidden="true"></i></button>
        </a>
    @endif
    @if( isset( $suspend_url ) )
        <a href="javascript:void(0)" class="{!! empty( $suspend_url ) ? 'disabled' : '' !!}" title="Suspend" data-href="{!! empty( $suspend_url ) ? 'javascript:void(0)' : $suspend_url !!}" data-button="delete" id="suspendModal">
            <button type="button" class="mr-1 btn btn-icon btn-danger btn-sm" style="width: 40px; background-color: #FFA500; border-color: #FFA500;"><i class="fa fa-power-off" aria-hidden="true"></i>
            </button>
        </a>
    @endif
</div>
