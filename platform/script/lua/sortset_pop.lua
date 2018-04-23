--[[
	sort set pop 从大到小
	cmd 支持 zrange, ZREVRANGE操作
]]

local data_key = KEYS[1]
local cmd = string.lower(KEYS[2])
local o_start = ARGV[1]
local o_end = ARGV[2]

if cmd ~= 'zrange' and cmd ~= 'zrevrange' then
	return false
end

--获取数据
local data = redis.call(cmd, data_key, o_start, o_end, 'WITHSCORES')
if data == nil then
	return false
end


--筛选成员, 删除数据
for k in pairs(data) do
	if k % 2 ~= 0 then
		redis.call('zrem', data_key, data[k])
	end
end

return data