@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/css/ribbon-page.css') }}">
    <style>
        .sticky-element {
            position: sticky;
            top: 80px;
            z-index: 8;

            .window-scrolled & {
                inset-block-start: 0;
            }

            .layout-navbar-fixed & {
                inset-block-start: calc($navbar-height - .4375rem);
            }

            .layout-menu-fixed .layout-horizontal & {
                inset-block-start: calc($navbar-height * 2 - .25rem);
            }

            .layout-menu-fixed.layout-navbar-fixed .layout-horizontal & {
                inset-block-start: $navbar-height;
            }
        }
    </style>
@endpush

@section('content')
    <form id="formRoles">
        @csrf

        <div class="card-header sticky-element bg-light p-3 shadow">

            <div class="row col-md-12 ">
                <div class="input-group">
                    <input type="hidden" id="idrole" name="idrole" class="form-control" readonly
                        value="{{ $roleId ?? null }}">
                    <input type="text" id="name" name="name" class="form-control text-uppercase"
                        placeholder="Nama Role / Nama Level" required autocomplete="off"
                        value="{{ $rolename->name ?? null }}"
                        {{ Auth::user()->hasRole('SUPER ADMINISTRATOR') ? '' : 'readonly' }}>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>


            </div>

        </div>

        <div class="card-body">

            <div id="resultPermission" class="mt-3">
                @include('roles._listperm', ['permissions' => $permissions])
            </div>
        </div>

    </form>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/roles/kelola-roles.js') }}"></script>
@endpush
