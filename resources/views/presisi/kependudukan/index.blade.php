@extends('layouts.presisi.index')

@section('content_header')
    <h2>Dashboard Data Presisi</h2>
@stop

@section('content')
    <div class="row">
        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
            <div class="info-box shadow-none rounded-0">
                <div class="info-box-content">
                    <div class="row">
                        <div class="col-4">
                            <div class="card card-primary rounded-0 elevation-0 border">
                                <div class="card-header rounded-0">
                                    <h3 class="card-title">Statistik Penduduk</h3>

                                </div>
                                <div class="card-body p-0 ">
                                    <ul class="nav nav-pills flex-column">
                                        @foreach ($statistik as $key => $sub)
                                            <li class="nav-item active">
                                                <a class="nav-link" data-key="{{ $key }}">
                                                    <i class="fas fa-inbox"></i> {{ $sub }}
                                                    <span class="badge bg-primary float-right">12</span>
                                                </a>
                                            </li>
                                        @endforeach

                                         

                                    </ul>
                                </div>

                            </div>
                        </div>
                        <div class="col-8">
                            <div class="card card-primary card-outline rounded-0 elevation-0 border">
                                <div class="card-header">
                                    <h3 class="card-title">Tabel</h3>


                                </div>

                                <div class="card-body p-0">

                                    <div class="table-responsive mailbox-messages">
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Penyandang Cacat	</th>
                                                    <th>Jumlah</th>
                                                    <th>Laki - laki</th>
                                                    <th>Perempuan</th>
                                                     
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check1">
                                                            <label for="check1"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find a
                                                        solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"></td>
                                                    <td class="mailbox-date">5 mins ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check2">
                                                            <label for="check2"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star-o text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">28 mins ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check3">
                                                            <label for="check3"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star-o text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">11 hours ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check4">
                                                            <label for="check4"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"></td>
                                                    <td class="mailbox-date">15 hours ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check5">
                                                            <label for="check5"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">Yesterday</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check6">
                                                            <label for="check6"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star-o text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">2 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check7">
                                                            <label for="check7"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star-o text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">2 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check8">
                                                            <label for="check8"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"></td>
                                                    <td class="mailbox-date">2 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check9">
                                                            <label for="check9"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"></td>
                                                    <td class="mailbox-date">2 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check10">
                                                            <label for="check10"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star-o text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"></td>
                                                    <td class="mailbox-date">2 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check11">
                                                            <label for="check11"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star-o text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">4 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check12">
                                                            <label for="check12"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"></td>
                                                    <td class="mailbox-date">12 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check13">
                                                            <label for="check13"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star-o text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">12 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check14">
                                                            <label for="check14"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">14 days ago</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" value="" id="check15">
                                                            <label for="check15"></label>
                                                        </div>
                                                    </td>
                                                    <td class="mailbox-star"><a href="#"><i
                                                                class="fas fa-star text-warning"></i></a></td>
                                                    <td class="mailbox-name"><a href="#">Alexander Pierce</a>
                                                    </td>
                                                    <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find
                                                        a solution to this problem...
                                                    </td>
                                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                                    <td class="mailbox-date">15 days ago</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </div>

                                </div>

                                <div class="card-footer p-0">
                                    <div class="mailbox-controls">

                                        <button type="button" class="btn btn-default btn-sm checkbox-toggle">
                                            <i class="far fa-square"></i>
                                        </button>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-sm">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-default btn-sm">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                            <button type="button" class="btn btn-default btn-sm">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </div>

                                        <button type="button" class="btn btn-default btn-sm">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <div class="float-right">
                                            1-50/200
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default btn-sm">
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <button type="button" class="btn btn-default btn-sm">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>



@endsection
