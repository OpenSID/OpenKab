<script nonce="{{ csp_nonce() }}">

    const header = @include('layouts.components.header_bearer_api_gabungan');

    function group() {
        _.mixin({
            'mergeByKey': function(arr1, arr2, key) {
                var criteria = {};
                criteria[key] = null;
                return _.map(arr1, function(item) {
                    criteria[key] = item[key];
                    let find = _.find(arr2, criteria)

                    if (find) {
                        item.selected = true;
                        _.forEach(['read', 'write', 'edit', 'delete'], function(value) {
                            if (find[find.permission + '-' + value] == 'true') {
                                item[item.permission + '-' + value] = true;
                            }
                        })

                        if (find.submenu != undefined) {

                            var merged = _.merge(_.keyBy(item.submenu, 'permission'), _.keyBy(find.submenu, 'permission'));
                            let submenu = _.values(merged);
                            let submenu_selected = _.map(submenu, function(value) {
                                _.forEach(['read', 'write', 'edit', 'delete'], function(permission) {
                                    if (value[value.permission + '-' + permission] == 'true') {
                                        value[value.permission + '-' + permission] = true;
                                    }
                                })
                                if (value.selected == 'true') {
                                    value.selected = true;
                                }
                                return value
                            })
                            item.submenu = submenu_selected
                        }
                    }
                    return item;
                });
            }
        });

        return {
            dataGroup: {
                'name': '',
                'menu': new Array(),
                'id': null
            },
            menu: {},
            retrieveData() {
                this.retriveMenu();
            },

            retriveMenu() {

                var url = new URL("{{ config('app.databaseGabunganUrl') }}/api/v1/pengaturan/group/menu");

                fetch(url, {
                        method: "GET",
                        headers: header
                    })
                    .then(res => res.json())
                    .then(response => {
                        this.retriveGroup(response.data)
                    });
            },
            retriveGroup(menu) {
                    var url = new URL("{{ config('app.databaseGabunganUrl') }}/api/v1/pengaturan/group/show/{{$id}}");
                    fetch(url, {
                        method: "GET",
                        headers: header
                    })
                    .then(res => res.json())
                    .then(response => {
                        this.dataGroup.name = response.data.attributes.name;
                        this.dataGroup.id = response.data.id;
                        this.menu = _.mergeByKey(menu, response.data.attributes.menu, 'permission');
                    });
            },
            simpan() {
                if (isEmpty(_.trim(this.dataGroup.name))) {
                    Swal.fire(
                            'Error!  ',
                            'Nama grup harus diisi',
                            'error'
                        )
                    return
                }
                let menu = _.chain(this.menu).map(function(menu) {
                    if (menu.submenu && menu.selected) {
                        let submenu = _.chain(menu.submenu).filter(function(_submenu) {
                            if (_submenu.selected) {
                                return _submenu;
                            }
                        }).value();
                        if (submenu.length > 0) {
                            menu.submenu = submenu
                        }
                    }
                    return menu;
                }).filter(function(menu) {
                    if (menu.submenu && menu.submenu.length > 0 && menu.selected) {
                        return menu
                        if (submenu.length > 0) {
                            menu.submenu = submenu
                            return menu.selected = false
                        }
                    } else if (menu.selected) {
                        return menu;
                    }
                }).value()

                this.dataGroup.menu = menu;
                if (isEmpty(this.dataGroup.menu)) {
                    Swal.fire(
                            'Error!  ',
                            'Tidak ada menu yang dipilih',
                            'error'
                        )
                    return
                }
                Swal.fire({
                    title: 'Menyimpan',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                })
                var data = this.dataGroup;

                var url = new URL("{{ config('app.databaseGabunganUrl') }}/api/v1/pengaturan/group/{{ $id }}");

                $.ajax({
                    type: "PUT",
                    url: url,
                    headers: header,
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
                            window.location.replace("{{ url('pengaturan/groups') }}");
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
            selected(data) {
                _.forEach(['read', 'write', 'edit', 'delete'], function(value) {
                    data[data.permission + '-' + value] = data.selected;
                })
                if (data.submenu) {
                    data.submenu = _.chain(data.submenu).map(function(submenu) {
                        _.forEach(['read', 'write', 'edit', 'delete'], function(value) {
                            submenu[submenu.permission + '-' + value] = data.selected;
                        })
                        submenu.selected = data.selected;
                        return submenu;
                    }).value();
                }
            },
            selected_sub(data) {
                let selected = _.chain(data.submenu).filter(function(menu) {
                    _.forEach(['read', 'write', 'edit', 'delete'], function(value) {
                        menu[menu.permission + '-' + value] = menu.selected;
                    })
                    if (menu.selected) {

                        return menu
                    }
                }).value();
                if (selected.length == 0) {
                    _.forEach(['read', 'write', 'edit', 'delete'], function(value) {
                        data[data.permission + '-' + value] = false;
                    })
                    data.selected = false;
                } else {
                    _.forEach(['read', 'write', 'edit', 'delete'], function(value) {
                        data[data.permission + '-' + value] = true;
                    })

                    data.selected = true;
                }
            }
        }
    }
</script>
