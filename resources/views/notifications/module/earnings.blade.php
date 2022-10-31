## 收益汇报
# {{ $module->name }}
==================================
## 本月
#### 现金 {{ round($data['transactions']['this_month']['balance'], 2) }} 元
#### Drops {{ round($data['transactions']['this_month']['drops'], 4) }}
==================================
## 上个月
#### 现金 {{ round($data['transactions']['last_month']['balance'], 2) }} 元
#### Drops {{ round($data['transactions']['last_month']['drops'], 4) }}


{{--
$module = $this->http->get('modules')->json()['data'];

$total = $module['transactions']['this_month']['balance'];

$drops = $module['transactions']['this_month']['drops'] / $module['rate'];

if ($drops < 0) { $drops=0; } $total +=$drops; $total=round($total, 2); $module=[ 'balance'=>
    $module['transactions']['this_month']['balance'],
    'drops' => $module['transactions']['this_month']['drops'],
    'total' => $total,
    ];

    <h4>收益</h4>
    <div>
        <h3>
            本月收益
        </h3>
        <p>
            直接扣费金额: {{ $module['balance'] }} 元
        </p>
        <p>
            Drops: {{ $module['drops'] }}
        </p>
        <p>本月总计收入 CNY: {{ $module['total'] }} </p>
    </div> --}}
