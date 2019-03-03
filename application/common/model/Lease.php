<?php
namespace app\common\model;
use think\Model;

class Lease extends Model
{
	public function Admin()
	{
		if ($this->getData('admin_id') == "")
			return Admin::get($this->Room()->getData('admin_id'));
		return Admin::get($this->getData('admin_id'));
	}

	public function User()
	{
		return User::get($this->getData('user_id'));
	}

	public function Room()
	{
		return Room::get($this->getData('room_id'));
	}

	public function getSweepAttr()
	{
		return $this->getData('sweep') == false ? '无' : '有';
	}

	public function getEntertainAttr()
	{
		return $this->getData('entertain') == false ? '无' : '有';
	}

	public function getStartTimeAttr()
	{
		$date = $this->getData('start_time');
		if ($date == '0')
			$date = time();
		return date('Y-m-d', $date) . 'T' . date('H:m', $date);
	}

	public function getEndTimeAttr()
	{
		$date = $this->getData('end_time');
		if ($date == '0')
			$date = time()+3600*2;
		return date('Y-m-d', $date) . 'T' . date('H:m', $date);
	}

	public function getFinishTimeAttr()
	{
		$date = $this->getData('finish_time');
		if ($date == '0')
			return '0';
		return date('Y-m-d', $date) . 'T' . date('H:m', $date);
	}

	public function getStartTimeShowed()
	{
		$date = $this->getData('start_time');
		if ($date == 0) return "未设置";
		return date('Y-m-d H:m', $date);
	}

	public function getEndTimeShowed()
	{
		$date = $this->getData('end_time');
		if ($date == 0) return "未设置";
		return date('Y-m-d H:m', $date);
	}

	public function getFinishTimeShowed()
	{
		$date = $this->getData('finish_time');
		if ($date == 0) return "未设置";
		return date('Y-m-d H:m', $date);
	}

	/**
	 * 获取现在的时间，并格式化字符串
	 * 设置为整点或者整点半
	 * @param  integer $delta 偏差值（一般为正）
	 * @return string         时间
	 */
	static public function getSuitableTime($delta=0)
	{
		$date = time();
		$hour = (int)date('H', $date);
		$minute = (int)date('i', $date);

		// 设置成下一个整点
		$date += 60 * (60 - $minute);
		$minute = 0;
		$hour++;

		// 再设置成一个小时后
		$hour++;
		$date += 3600 * 1;

		// 判断是否符合开会的时间
		if ($hour >= 10 && $hour <= 14)
		{
			$date += 3600 * (14 - $hour);
			$hour = 14;
		}
		else if ($hour > 16 && $hour < 18)
		{
			$date += 3600 * (19 - $hour);
			$hour = 19;
		}
		else if ($hour >=  20 && $hour < 24) // 晚上
		{
			$date += 3600 * (24-$hour+8); // 设置为明早8点
			/*if ($minute == 30) // 暂时用不到了
				$date -= 60 * 30;*/
			$hour = 8;
		}
		else if ($hour >= 24)  // 23:?? ，设置为明早8点
		{
			$date += 3600 * (8 + 24 - $hour);
			$hour = 8;
		}

		$date += $delta; // 加上偏差值

		return date('Y-m-d', $date) . 'T' . date('H:i', $date);
	}

	public function canFinish()
	{
		$start_time = $this->getData('start_time');
		$finish_time = $this->getData('finish_time');
		$end_time = $this->getData('end_time');
		$time = time();

		if ($start_time < $time && $finish_time > $time)
			return true;
		return false;
	}

	public function toString()
	{
		$str = '<lease_id>' . $this->getData('lease_id') . '</lease_id>'
			 . '<room_id>' . $this->getData('room_id') . '</room_id>'
			 . '<admin_id>' . $this->getData('admin_id') . '</admin_id>'
			 . '<room_name>' . $this->Room()->getData('name') . '</room_name>'
			 . '<admin_name>' . $this->Admin()->getName() . '</admin_name>'
			 . '<start_time>' . $this->getData('start_time') . '</start_time>'
			 . '<finish_time>' . $this->getData('finish_time') . '</finish_time>'
			 . '<theme>' . $this->getData('theme') . '</theme>'
			 . '<usage>' . $this->getData('usage') . '</usage>'
			 . '<message>' . $this->getData('message') . '</message>'
			 . '<sweep>' . $this->getData('sweep') . '</sweep>'
			 . '<entertain>' . $this->getData('entertain') . '</entertain>'
			 . '<remote>' . $this->getData('remote') . '</remote>'
			 . '<admin_score>' . $this->getData('admin_score') . '</admin_score>'
			 . '<user_score>' . $this->getData('user_score') . '</user_score>'
			 . '<credit_change>' . $this->getData('credit_change') . '</credit_change>'
			 . '<create_time>' . $this->getData('create_time') . '</create_time>'
			 . '<update_time>' . $this->getData('update_time') . '</update_time>';
		$str = "<lease>" . $str . "</lease>";

		return $str;
	}


}