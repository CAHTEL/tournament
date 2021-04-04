<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    </head>
    <body class="antialiased">
    <a class="btn btn-success btn-lg" href="{{route('start')}}" role="button">Generate new</a>
    <br>

    @foreach($groupData as $data)
        <div style="display: inline-block; margin-right: 100px">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th scope="row">TEAMS</th>

            @foreach($data['teamsNames'] as $key => $name)
                    <th scope="row">{{$name}}</th>
                @endforeach
                <th scope="row">Score</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data['teamsGames'] as $teamGames)
                <tr>
                    <td>{{$teamGames[0]['team_name']}}</td>
                    @foreach($teamGames as $game)
                        <td>{{$game['game_score']}}</td>
                    @endforeach
                    <td>{{$game['score']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @endforeach
    <br/>
    <div class="alert alert-dark" role="alert">
        <strong> Play OFF</strong>
    </div>
    @foreach($finalData as $data)
        <div style="display: inline-block; margin-right: 100px">

        <table class="table table-bordered" style="height: 250px">
            <thead>
        <tr>
            @foreach($data as $name)
                <tr><td>{{$name['team_name']}} ({{$name['game_score']}})</td><tr>
            @endforeach
        </tr>
        </thead>
    </table>
        </div>
    @endforeach
    <div style="display: inline;">

                <div class="alert alert-success" role="alert">
                    Winner! <strong>{{$winner}}</strong>
                </div>
    </div>

    </body>
</html>
