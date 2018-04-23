--[[
	命令行使用: EVALSHA sha1 2 {用户ID} [gold|credit|ticket|emerald] {数值}
	操作用户金币，钻石，奖券，绿宝石等数据使用
	使得更新时可以触发其他事件
]]
local onGoldChange = function(id, val)
	local key = 'modify_gold'
	redis.call('zadd', key, val, id)
end

local onCreditChange = function(id, val)
	local key = 'modify_credit'
	redis.call('zadd', key, val, id)
end

--当积分发生变化时
local onPointChange = function (id, val)
	--记录变更
	local key = 'modify_point'
	redis.call('ZADD', key, val, id)
end

local onEmeraldChange = function (id, val)
	local key = 'modify_emerald'
	redis.call('ZADD', key, val, id)
end

local onFrozenChange = function (id, val)
	local key = 'modify_frozen'
	redis.call('ZADD', key, val, id)
end

local onEagleeyeChange = function (id, val)
	local key = 'modify_eagleeye'
	redis.call('ZADD', key, val, id)
end

local onMaxbulletmulChage = function (id, val)
	local key = 'modify_maxbulletmul'
	redis.call('ZADD', key, val, id)
end

local onBulletunlocksituationChage = function (id, val)
	local key = 'modify_bulletunlocksituation'
	redis.call('ZADD', key, val, id)
end

local onBulletupsuccessrateChage = function (id, val)
	local key = 'modify_bulletupsuccessrate'
	redis.call('ZADD', key, val, id)
end

local onBulletlvChage = function (id, val)
	local key = 'modify_bulletlv'
	redis.call('ZADD', key, val, id)
end

local onBoublebulletunlockedChage = function (id, val)
	local key = 'modify_doublebulletunlocked'
	redis.call('ZADD', key, val, id)
end


--修正支持多个货币修改
local p_count = KEYS[1]

--原有系统的情况需要兼容
if (tonumber(p_count) > 10) then
	--这个时候就说明此情况是老版本的接口，传递的是user_id的值
	local user_id = KEYS[1]
	local data_type = KEYS[2]
	local value = ARGV[1]

	--动作
	local switch_action = {
		--金币
		['gold'] = function(id, val)
            local key = 'info@' .. id
			local ret = redis.call('hincrby', key, 'gold', val)

			if(ret and val ~= 0) then
				local total = redis.call('hget', key, 'gold')
				onGoldChange(id, total)
			end
			return ret
		end,

		--钻石
		['credit'] = function(id, val)
			local key = 'info@' .. id
			local ret = redis.call('hincrby', key, 'credit', val)
			if(ret and val ~= 0) then
				local total = redis.call('hget', key, 'credit')
				onCreditChange(id, total)
			end
			return ret
		end,

		 --一指赢积分
		['point'] = function(id, val)
            local key = 'info@' .. id
    		local ret = redis.call('hset', key, 'point', val)
    		if (ret == 0) then
    		    onPointChange(id, val)
    		end
    		return val
        end,

		--绿宝石
		['emerald'] = function(id, val)
			local key = 'info@' .. id
			local ret = redis.call('hincrby', key, 'emerald', val)
			if(ret and val ~= 0) then
				local total = redis.call('hget', key, 'emerald')
				onEmeraldChange(id, total)
			end
			return ret
		end,

		--道具冰封
		['frozen'] = function(id, val)
			local key = 'info@' .. id
			local ret = redis.call('hincrby', key, 'frozen', val)
			if(ret and val ~= 0) then
				local total = redis.call('hget', key, 'frozen')
				onFrozenChange(id, total)
			end
			return ret
		end,

		--道具鹰眼
		['eagleeye'] = function(id, val)
			local key = 'info@' .. id
			local ret = redis.call('hincrby', key, 'eagleeye', val)
			if(ret and val ~= 0) then
				local total = redis.call('hget', key, 'eagleeye')
				onEagleeyeChange(id, total)
			end
			return ret
		end,

		--奖券
		['ticket'] = function(id, val)
			local key = 'info@' .. id
			return redis.call('hincrby', key, 'ticket', val)
		end
	}

	local act = switch_action[data_type]
	if (act) then
		return act(user_id, value)
	end
	return nil
end

local hmset_action = {
	[2] = function(key, params)
		return redis.call('hMset', key, params[1], params[2])
	end,
	[4] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4])
	end,
	[6] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6])
	end,
	[8] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6], params[7], params[8])
	end,
	[10] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6], params[7],
		params[8], params[9], params[10])
	end,
	[12] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6], params[7],
		params[8], params[9], params[10], params[11], params[12])
	end,
	[14] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6], params[7],
		params[8], params[9], params[10], params[11], params[12],
		params[13], params[14])
	end,
	[16] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6], params[7],
		params[8], params[9], params[10], params[11], params[12],
		params[13], params[14], params[15], params[16])
	end,
	[18] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6], params[7],
		params[8], params[9], params[10], params[11], params[12],
		params[13], params[14], params[15], params[16], params[17],
		params[18])
	end,
	[20] = function(key, params)
		return redis.call('hMset', key, params[1], params[2],
		params[3], params[4], params[5], params[6], params[7],
		params[8], params[9], params[10], params[11], params[12],
		params[13], params[14], params[15], params[16], params[17],
		params[18], params[19], params[20])
	end
}

if (p_count and p_count ~= 0) then
	--保障能一次性修改所有值，边缘化排行的影响
	local u_redis = {}
	local key = 'info@' .. KEYS[2]
	for i=1, p_count
	do
		if (KEYS[2+i]=='point') then
		redis.call('hincrby', key, KEYS[2+i], 0)
		local u_count = tonumber(ARGV[i])
		table.insert(u_redis, KEYS[2+i])
		table.insert(u_redis, u_count)
		else
		redis.call('hincrby', key, KEYS[2+i], 0)
		local count = redis.call('hget', key, KEYS[2+i])
		local u_count = tonumber(count) + tonumber(ARGV[i])
		table.insert(u_redis, KEYS[2+i])
		table.insert(u_redis, u_count)
		end

	end

	local h_act = hmset_action[table.getn(u_redis)]
	local u_ret = h_act(key, u_redis)
	if (u_ret and u_ret ~= 0) then
	else
		return nil
	end

	for i = 1, p_count
	do
		local count = redis.call('hget', key, KEYS[2+i])
		if (KEYS[2+i] == 'gold') then
			onGoldChange(KEYS[2], count)
		elseif(KEYS[2+i] == 'credit') then
			onCreditChange(KEYS[2], count)
		elseif(KEYS[2+i] == 'point') then
			onPointChange(KEYS[2], count)
		elseif(KEYS[2+i] == 'emerald') then
			onEmeraldChange(KEYS[2], count)
		elseif(KEYS[2+i] == 'frozen') then
			onFrozenChange(KEYS[2], count)
		elseif(KEYS[2+i] == 'eagleeye') then
			onEagleeyeChange(KEYS[2], count)
		elseif(KEYS[2+i] == 'MaxBulletMul') then
			onMaxbulletmulChage(KEYS[2], count)
		elseif(KEYS[2+i] == 'BulletUnlockSituation') then
			onBulletunlocksituationChage(KEYS[2], count)
		elseif(KEYS[2+i] == 'BulletUpSuccessRate') then
			onBulletupsuccessrateChage(KEYS[2], count)
		elseif(KEYS[2+i] == 'BulletLv') then
			onBulletlvChage(KEYS[2], count)
		elseif(KEYS[2+i] == 'DoubleBulletUnlocked') then
			onBoublebulletunlockedChage(KEYS[2], count)
		else
		end
	end

	return u_ret
end

return nil
