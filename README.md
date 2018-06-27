# Rules

blacklist Response code 403
whitelist
ratelimit (per hour, per day, per minute, per second) Response code 429
patching


## Rules Paramters
ips
countries
user_agents
http_referers
http_cookies
url_paths
headers
query_strings
request_body

## Patching Parameters:
headers (inject, modify, delete)
query_strings (inject, modify, delete)
reqest_body (inject, modify, delete)




# Order of execution:

Rules:
whitelist
blacklist
ratelimit
patching
