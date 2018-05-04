@extends('emails.layouts.main')

@section('content')
    <table style="margin: 0 auto;" cellspacing="0" cellpadding="0" class="force-width-80">
        <tr>
            <td style="text-align:left; color: #6f6f6f;" class="spaced-out-lines">
                <br>
                {{$line1}}
                <br>
                {{$line2}}
                <br>
                <br>
            </td>
        </tr>
    </table>
@endsection
