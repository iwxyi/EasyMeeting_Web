<?php
namespace app\common\model;
use think\Model;

class Room extends Model
{
	public function Admin()
	{
		return $this->belongsTo('Admin');
	}

	/**
	 * 获取特定时间的空闲会议室
	 */
	static public function getFreeRooms($start_time = 0, $end_time = 0)
	{
		$sweep_time = 1800 * 1000; // 设置清理时间半个小时

		// 获取所有有关时间的租约订单
		$leases = new Lease;
		$leases->where("start_time  <= '$end_time' + $sweep_time && finish_time >= '$start_time' - $sweep_time");
		$leases = $leases->select();
		$busy_ids = array();
		foreach ($leases as $lease) {
			$room_id = $lease->getData('room_id');
			$busy_ids[$room_id] = 1;
		}

		// 获取所有空闲的会议室
		$free_rooms = Room::all();
		for ($i = 0; $i < count($free_rooms); $i++)
		{
			$room_id = $free_rooms[$i]->getData('room_id');
			if (array_key_exists($room_id, $busy_ids))
				array_splice($free_rooms, $i--, 1);
		}

		return $free_rooms;
	}

	/**
	 * 获取一个没有被使用的会议室
	 * 注意：还要留出前后半个小时打扫卫生
	 * @param int $start_time 会议开始时间
	 * @param int $end_time   会议最晚时间（不是结束时间）
	 */
	static public function getIntelliDistribute($start_time, $end_time)
	{
		$free_rooms = Room::getFreeRooms($start_time, $end_time);

		$count = count($free_rooms);

		$index = rand(0, $count);

		return $free_rooms[$index]->getData('room_id');
	}
}