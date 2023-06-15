<script>
    function group() {
        return {
            dataGroup: {
                'name': '',
                'menu': new Array()
            },
            menu: {},
            retrieveData() {
                this.retriveMenu();
            },

            retriveMenu() {
                fetch('{{ url('api/v1/pengaturan/group/menu') }}')
                    .then(res => res.json())
                    .then(response => {
                        this.menu = response.data
                    });
            },
            simpan() {
                let menu = _.chain(this.menu).filter(function (menu) {
                    if (menu.submenu && menu.selected) {
                        let submenu = _.chain(menu.submenu).filter(function (_submenu) {
                            if (_submenu.selected) {
                                return _submenu;
                            }
                        }).value();
                        if (submenu.length > 0) {
                            return submenu;
                        }

                    }else if(menu.selected) {
                        return menu
                    }
                }).value()
                this.dataGroup.menu = menu;
                Swal.fire({
                    title: 'Menyimpan',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                })
                var data = this.dataGroup;
                console.log(data)
                $.ajax({
                    type: "Post",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ url('api/v1/pengaturan/group') }}',
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response.success == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Data berhasil ditambahkan',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            window.location.replace("{{ url('penduduk') }}");
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            )
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        console.log('erer')
                        Swal.fire(
                            'Error!  ' + xhr.status,
                            JSON.parse(xhr.responseText).message,
                            'error'
                        )

                    }
                });
            },
            selected(data){
                if (data.submenu) {
                    data.submenu = _.chain(data.submenu).map(function (value) {
                        value.selected = data.selected;
                        return value;
                    }).value();
                }
            },
            selected_sub(data) {
                let selected = _.chain(data.submenu).filter(function (menu) {
                    if (menu.selected) {

                        return menu
                    }
                }).value();
                if (selected.length == 0 ) {
                    data.selected = false;
                }else{
                    data.selected = true;
                }

            }
        }
    }
</script>
