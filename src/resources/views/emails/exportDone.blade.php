@extends('emails.layouts.main')

@section('content')
    <table style="margin: 0 auto;" cellspacing="0" cellpadding="0" class="force-width-80">
        <tr>
            <td style="text-align:left; color: #6f6f6f;" class="spaced-out-lines">
                @foreach($lines as $line)
                <p>
                    {{$line}}
                <p>
                @endforeach
            </td>
        </tr>
    </table>
@endsection
