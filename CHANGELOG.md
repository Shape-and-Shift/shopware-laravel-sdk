### 2.0.0
- Drop primary key from `shop_id` of `sw_shop`
- Add `app_id` as primary in `sw_shop`
- Change behavior of middleware (using `app_name` and `app_id`)

### 1.3.2
- Add get middleware for Iframe

### 1.3.1
- Add SwAppHeaderMiddleware to verify incoming headers requests from Shopware

### 1.3.0
- Add SwAppIframeMiddleware to verify incoming requests from Iframe Shopware

### 1.2.1
- Fix wrong shopId parameter

### 1.2.0
- Update SwAppMiddleware check post request key parameter same as get queries

### 1.1.0
- Changed the version of the shopware-sdk to 1.*

### 1.0.0
- Package initial structure
- First release
