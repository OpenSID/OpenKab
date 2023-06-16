<script>
    function group() {
        _.mixin({
            'mergeByKey': function(arr1, arr2, key) {
                var criteria = {};
                criteria[key] = null;
                return _.map(arr1, function(item) {
                    criteria[key] = item[key];
                    let find = _.find(arr2, criteria)
                    if (find) {
                        find.selected = true;
                        if (find.submenu != undefined) {
                            let submenu = _.map(find.submenu, function (submenu) {
                                submenu.selected = true;
                                return submenu
                            })
                            find.submenu= submenu
                        }
                        return find;
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
                fetch('{{ url('api/v1/pengaturan/group/menu') }}')
                    .then(res => res.json())
                    .then(response => {
                        // this.menu = response.data
                        this.retriveGroup(response.data)
                    });
            },
            retriveGroup(menu) {
                fetch('{{ url('api/v1/pengaturan/group/show/' . $id) }}')
                    .then(res => res.json())
                    .then(response => {
                        this.dataGroup.name = response.data.attributes.name;
                        this.dataGroup.id = response.data.id;
                        this.menu = _.mergeByKey(menu, response.data.attributes.menu, 'role');
                    });
            },
            simpan() {
                let menu = _.chain(this.menu).filter(function(menu) {
                    if (menu.submenu && menu.selected) {
                        let submenu = _.chain(menu.submenu).filter(function(_submenu) {
                            if (_submenu.selected) {
                                return _submenu;
                            }
                        }).value();
                        if (submenu.length > 0) {
                            return submenu;
                        }

                    } else if (menu.selected) {
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

                $.ajax({
                    type: "PUT",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ url('api/v1/pengaturan/group/' . $id) }}',
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
                if (data.submenu) {
                    data.submenu = _.chain(data.submenu).map(function(value) {
                        value.selected = data.selected;
                        return value;
                    }).value();
                }
            },
            selected_sub(data) {
                let selected = _.chain(data.submenu).filter(function(menu) {
                    if (menu.selected) {

                        return menu
                    }
                }).value();
                if (selected.length == 0) {
                    data.selected = false;
                } else {
                    data.selected = true;
                }
            }
        }
    }
</script>
