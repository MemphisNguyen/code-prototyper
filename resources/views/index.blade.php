<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Prototyper</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Create Vue template</h1>
            </div>
        </div>
        <div class="row">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col-12">
                <form action="{{ env('APP_URL', 'http://localhost') }}/submit" method="post">
                    @csrf
                    <div class="form-group row">
                        <label for="name" class="col-sm-2 offset-sm-2">Component name (*)</label>
                        <div class="col-sm-6">
                            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                            <small class="form-text text-muted">e.g: Booking Passenger</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="sub_folder" class="col-sm-2 offset-sm-2">Contain folder</label>
                        <div class="col-sm-6">
                            <input type="text" name="sub_folder" class="form-control" value="{{ old('sub_folder') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="table" class="col-sm-2 offset-sm-2">DB Table (*)</label>
                        <div class="col-sm-6">
                            <input type="text" name="table" class="form-control" required value="{{ old('table') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="mul_lang" class="col-sm-2 offset-sm-2">Multi-language?</label>
                        <div class="col-sm-6">
                            <input type="checkbox" name="mul_lang" value="1">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="sub_table" class="col-sm-2 offset-sm-2">Relative Table</label>
                        <div class="col-sm-6">
                            <input type="text" name="sub_table" class="form-control" value="{{ old('sub_table') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="api_uri" class="col-sm-2 offset-sm-2">API path (*)</label>
                        <div class="col-sm-6">
                            <div class="row form-inline">
                                <div class="col-12">
                                    http://domain.com/
                                    <input type="text" name="api_uri" class="form-control mb-2" required  value="{{ old('api_uri') }}"> /
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="display_field" class="col-sm-2 offset-sm-2">Table's field to display (*)</label>
                        <div class="col-sm-6">
                            <input type="text" name="display_field" class="form-control" required  value="{{ old('display_field') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="sub_field" class="col-sm-2 offset-sm-2">Table's sub-field to display</label>
                        <div class="col-sm-6">
                            <input type="text" name="sub_field" class="form-control"  value="{{ old('sub_field') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 offset-2 offset-sm-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
