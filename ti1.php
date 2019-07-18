<?php


/*  TODO 1
 * 接入支付大致流程以及都有什么需要注意的
 * 简单描述即可
 *
 */

/* TODO 2
	列出一段自认为最擅长的业务场景，  和简短的伪代码，  并加以说明

*/

/* TODO 3
 *
 * 尝试优化（修改时使用伪代码即可）， 并说明理由
 *
 * 字段解释
 * Bank（laravel 框架中的model）
 *  payment_platform 三方名称标识
 *  name 名称
 *  mode 渠道（如支付宝，微信）
 *
 * Deposit（laravel 框架中的model）
 *  deposit_channel 三方名称
 *  deposit_mode 渠道（如支付宝，微信）
 *  status 状态 3 表示完成
 */



$Banks = Bank::where('status', 1)->get(['id', 'payment_platform', 'name', 'mode'])->toArray();
foreach ($Banks as $bank) {
    $paymentName = Bank::$aPaymentPlatforms[$bank['payment_platform']]; //获取三方名称
    $key = "{$paymentName}{$bank['mode']}";

    $all_orders = isset($all_orders_obg[$key]) ? $all_orders_obg[$key]['total_count'] : 0;
    $succ_orders = isset($all_orders_obg[$key]) ? $all_orders_obg[$key]['success_count'] : 0;
    $all_orders = Deposit::where('deposit_channel', $paymentName)
        ->where('deposit_mode', $bank->mode)
        ->whereBetween('created_at', [$begin, $now])
        ->count();
    if (!$all_orders) continue;  //没有充值订单,跳出本次循环
    $succ_orders = Deposit::where('deposit_channel', $paymentName)
        ->where('deposit_mode', $bank->mode)
        ->where('status', 3)
        ->whereBetween('created_at', [$begin, $now])
        ->count();


    if ($all_orders === 0) {
        continue;
    }


    $res_rate = round($succ_orders / $all_orders, 4); //当前渠道成功率
    if ($res_rate >= $least_succ_rate) {
        continue;
    }
    $res_rate = $res_rate * 100 ? $res_rate * 100 .'%' : 0;

    $msg .= "~~~~~~~~~~~~~~~\n\n第三方名称:\n{$paymentName}\n\n渠道名称:{$bank['name']}\n\n总订单数: {$all_orders}\n\n成功订单数: {$succ_orders}\n\n成功率: {$res_rate}\n\n时间段:\n{$begin} ~ {$now}\n\n";
}
if ($msg) {
    $pData = [
        'towhere' => '',
        'message' => $message . $msg,
        'type' => 'text',
        'filename' => '',
    ];

    $res = $this->curl($url , $pData);
}

/* TODO 4
 * 举出3～5个自认为最实用/有用的Linux命令，  并说一说为什么
 */
