#! /bin/bash

redis_client="redis-cli"
lua_file=(
"user_data.lua"
"EVALSHA@USER_DATA"
)

#regist lua script
for((i=0;i<${#lua_file[*]};i+=2))
do
	if [ ! -f ${lua_file[$i]} ]; then
		echo "file(${lua_file[$i]}) does not exists"
	else
		content=$(cat ${lua_file[$i]})
		sha1=$($redis_client script load "$content")
 		$redis_client set ${lua_file[$i+1]} $sha1
		echo "${lua_file[$i]} load success: $sha1"
	fi
done
