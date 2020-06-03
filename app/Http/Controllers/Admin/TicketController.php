<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Http\Controllers\Controller;
use App\Mail\closeTicket;
use App\Mail\replyTicket;
use App\Models\Ticket;
use App\Models\TicketReply;
use Auth;
use Illuminate\Http\Request;
use Mail;
use Response;

/**
 * 工单控制器
 *
 * Class TicketController
 *
 * @package App\Http\Controllers\Controller
 */
class TicketController extends Controller {
	protected static $systemConfig;

	function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	// 工单列表
	public function ticketList(Request $request) {
		$email = $request->input('email');

		$query = Ticket::query();

		if(isset($email)){
			$query->whereHas('user', function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		$view['ticketList'] = $query->orderByDesc('id')->paginate(10)->appends($request->except('page'));

		return Response::view('admin.ticket.ticketList', $view);
	}

	// 回复工单
	public function replyTicket(Request $request) {
		$id = $request->input('id');

		if($request->isMethod('POST')){
			$content = clean($request->input('content'));
			$content = str_replace("eval", "", str_replace("atob", "", $content));
			$content = substr($content, 0, 300);

			$obj = new TicketReply();
			$obj->ticket_id = $id;
			$obj->user_id = Auth::id();
			$obj->content = $content;
			$obj->save();

			if($obj->id){
				// 将工单置为已回复
				$ticket = Ticket::query()->with(['user'])->whereId($id)->first();
				$ticket->status = 1;
				$ticket->save();

				$title = "工单回复提醒";
				$content = "标题：".$ticket->title."<br>管理员回复：".$content;

				// 发通知邮件
				if(!Auth::getUser()->is_admin){
					if(self::$systemConfig['webmaster_email']){
						$logId = Helpers::addNotificationLog($title, $content, 1,
							self::$systemConfig['webmaster_email']);
						Mail::to(self::$systemConfig['webmaster_email'])->send(new replyTicket($logId, $title,
							$content));
					}
					// 推送通知管理员
					PushNotification::send($title, $content);
				}else{
					$logId = Helpers::addNotificationLog($title, $content, 1, $ticket->user->email);
					Mail::to($ticket->user->email)->send(new replyTicket($logId, $title, $content));
				}

				return Response::json(['status' => 'success', 'data' => '', 'message' => '回复成功']);
			}else{
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '回复失败']);
			}
		}else{
			$view['ticket'] = Ticket::query()->whereId($id)->with('user')->first();
			$view['replyList'] = TicketReply::query()->whereTicketId($id)->with('user')->orderBy('id')->get();

			return Response::view('admin.ticket.replyTicket', $view);
		}
	}

	// 关闭工单
	public function closeTicket(Request $request) {
		$id = $request->input('id');

		$ticket = Ticket::query()->with(['user'])->whereId($id)->first();
		if(!$ticket){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '关闭失败']);
		}

		$ticket->status = 2;
		$ret = $ticket->save();
		if(!$ret){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '关闭失败']);
		}

		$title = "工单关闭提醒";
		$content = "工单【".$ticket->title."】已关闭";

		// 发邮件通知用户
		$logId = Helpers::addNotificationLog($title, $content, 1, $ticket->user->email);
		Mail::to($ticket->user->email)->send(new closeTicket($logId, $title, $content));

		return Response::json(['status' => 'success', 'data' => '', 'message' => '关闭成功']);
	}

}
