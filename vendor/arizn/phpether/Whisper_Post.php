<?php
namespace phpEther;

/**
 * 	Ethereum whisper post object
 */
class Whisper_Post
{
	private $from, $to, $topics, $payload, $priority, $ttl;
	
	function __construct($from, $to, $topics, $payload, $priority, $ttl)
	{
		$this->from = $from;
		$this->to = $to;
		$this->topics = $topics;
		$this->payload = $payload;
		$this->priority = $priority;
		$this->ttl = $ttl;
	}
	
	function toArray()
	{
		return array(
			array
			(
				'from'=>$this->from,
				'to'=>$this->to,
				'topics'=>$this->topics,
				'payload'=>$this->payload,
				'priority'=>$this->priority,
				'ttl'=>$this->ttl
			)
		);
	}
}