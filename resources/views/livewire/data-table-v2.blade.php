<div>
    @if ($dataset != null)
        <table class="table">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($dataset as $row)
                    <tr>
                        @foreach ($row as $key => $collum)
                            <td>{{ $collum }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>{{ __('Nebyly nalezeny data') }}</p>
    @endif
</div>
