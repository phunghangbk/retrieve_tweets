<!DOCTYPE html>
<html>
<head>
  <title>Twitter API</title>
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
  <div class="container">
    <h2>Twitter Search API</h2>
    <form method="POST" action="{{route('post.gettweets')}}" enctype="multipart/form-data">
      {{ csrf_field() }}
      @if($errors->any())
        <div class="alert alert-danger">
          <strong>Whoops!</strong> There were some problems with your input.
          <br/>
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <div class="form-group">
        <label>Keyword:</label>
        <input type="text" name="keyword" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Start Time:</label>
        <input type="text" name="start_time" class="form-control">
      </div>
      <div class="form-group">
        <label>End Time:</label>
        <input type="text" name="end_time" class="form-control">
      </div>
      <div class="form-group">
        <button class="btn btn-success">Search</button>
      </div>
    </form>

    <table class="table table-bordered">
      <thead>
          <tr>
            <th width="50px">No</th>
            <th>Twitter Id</th>
            <th>Message</th>
            <th>Images</th>
            <th>Favorite</th>
            <th>Retweet</th>
          </tr>
      </thead>
      <tbody>
          @if(!empty($tweets))
            @foreach($data as $key => $value)
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>{{ $value['id'] }}</td>
                    <td>{{ $value['text'] }}</td>
                    <td>
                        @if(!empty($value['extended_entities']['media']))
                            @foreach($value['extended_entities']['media'] as $v)
                                <img src="{{ $v['media_url_https'] }}" style="width:100px;">
                            @endforeach
                        @endif
                    </td>
                    <td>{{ $value['favorite_count'] }}</td>
                    <td>{{ $value['retweet_count'] }}</td>
                </tr>
            @endforeach
          @else
            <tr>
              <td colspan="6">There are no data.</td>
            </tr>
          @endif
      </tbody>
    </table>
</div>


</body>
</html>