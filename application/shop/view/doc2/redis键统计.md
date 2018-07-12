
## redis键统计

注意：*{}这样的语法仅仅是强调，在键中，花括号是没有的。*

 
| 键         |库id| 类型 |含义  |
| -------- |:------|:------|
|rank:1    | 11 | zset | 波币排行，值是单纯的uid  |
|rank:2    | 11 | zset | 粉丝数排行，值是单纯的uid  |
|rank:3    | 11 | zset | 等级排行，值是单纯的uid  |
|rank:4    | 11 | zset | 拥有怪兽数排行，值是单纯的uid  |
|user:focus_count:{$uid}    | 11 | int | uid用户的粉丝数  |
|user:focus_list:{$uid}    | 11 | set | uid用户关注了哪些人，值是uid，第一个值是无意义0  |
|user:lahei:{$uid}         | 11 | set | uid用户拉黑了哪些人，值是uid，第一个值是无意义0  |
|buy_movie:{$uid}          | 11 | set | uid用户购买了哪些视频，值是room_id，第一个值是无意义0  |





