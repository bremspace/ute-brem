<div class="mb-4">
    <ul class="nav nav-pills nav-fill bg-body-tertiary rounded-3 p-1">
        @php
            $acctTabs = [
                'ledger' => ['accounting.ledger', 'Buku Besar'],
                'journal' => ['accounting.journal', 'Jurnal Umum'],
                'chart' => ['accounting.chart', 'Bagan Akun'],
                'trial' => ['accounting.trial_balance', 'Neraca Saldo'],
                'pl' => ['accounting.profit_loss', 'Laba Rugi'],
                'bs' => ['accounting.balance_sheet', 'Neraca'],
            ];
        @endphp
        @foreach ($acctTabs as $key => [$route, $label])
            <li class="nav-item">
                <a class="nav-link rounded-2 {{ ($acctTab ?? $key) === $key ? 'active' : '' }}"
                   href="{{ route($route) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>
</div>
