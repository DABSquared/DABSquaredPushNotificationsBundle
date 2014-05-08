Step 5: Integration With iOS
=======================================

### Accepting pull requests with better iOS Docs.


In the AppDelegate file do the following:


### A)  Register For Notifications:
``` objective-c
-(void)applicationDidFinishLaunching:(UIApplication *)application {
    // Add registration for remote notifications
    [[UIApplication sharedApplication] registerForRemoteNotificationTypes:(UIRemoteNotificationTypeAlert | UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeSound)];

    // Clear application badge when app launches (optional)
    application.applicationIconBadgeNumber = 0;
}
```

### B)  Register With Server:

``` objective-c
/**
 * Fetch and Format Device Token and Register Important Information to Remote Server
 */
- (void)application:(UIApplication *)application didRegisterForRemoteNotificationsWithDeviceToken:(NSData *)devToken {

	#if !TARGET_IPHONE_SIMULATOR

	// Get Bundle Info for Remote Registration (handy if you have more than one app)
	NSString *appName = [[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleDisplayName"];
	NSString *appVersion = [[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleVersion"];

	// Check what Notifications the user has turned on.  We registered for all three, but they may have manually disabled some or all of them.
	NSUInteger rntypes = [[UIApplication sharedApplication] enabledRemoteNotificationTypes];

	// Set the defaults to disabled unless we find otherwise...
	BOOL *pushBadge = false;
	BOOL *pushAlert = false;
	BOOL *pushSound = false;

	// Check what Registered Types are turned on. This is a bit tricky since if two are enabled, and one is off, it will return a number 2... not telling you which
	// one is actually disabled. So we are literally checking to see if rnTypes matches what is turned on, instead of by number. The "tricky" part is that the
	// single notification types will only match if they are the ONLY one enabled.  Likewise, when we are checking for a pair of notifications, it will only be
	// true if those two notifications are on.  This is why the code is written this way
	if(rntypes == UIRemoteNotificationTypeBadge){
		pushBadge = true;
	}
	else if(rntypes == UIRemoteNotificationTypeAlert){
		pushAlert = true;
	}
	else if(rntypes == UIRemoteNotificationTypeSound){
		pushSound = true;
	}
	else if(rntypes == ( UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeAlert)){
		pushBadge = true;
		pushAlert = true;
	}
	else if(rntypes == ( UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeSound)){
		pushBadge = true;
		pushSound = true;
	}
	else if(rntypes == ( UIRemoteNotificationTypeAlert | UIRemoteNotificationTypeSound)){
		pushAlert = true;
		pushSound = true;
	}
	else if(rntypes == ( UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeAlert | UIRemoteNotificationTypeSound)){
		pushBadge = true;
		pushAlert = true;
		pushSound = true;
	}

	// Get the users Device Model, Display Name, Unique ID, Token & Version Number
	UIDevice *dev = [UIDevice currentDevice];
    NSString *deviceName = dev.name;
	NSString *deviceModel = dev.model;
	NSString *deviceSystemVersion = dev.systemVersion;
	NSString *deviceIdentifier = [UIDevice currentDevice].identifierForVendor;

	// Prepare the Device Token for Registration (remove spaces and < >)
	NSString *deviceToken = [[[[devToken description]
		stringByReplacingOccurrencesOfString:@"<"withString:@""]
		stringByReplacingOccurrencesOfString:@">" withString:@""]
		stringByReplacingOccurrencesOfString: @" " withString: @""];

	// Build URL String for Registration
	// !!! CHANGE "www.mywebsite.com" TO YOUR WEBSITE. Leave out the http://
	// !!! SAMPLE: "secure.awesomeapp.com"
	NSString *host = @"www.mywebsite.com";
	postString = [postString stringByAppendingString:@"?app_id=0000001"]; //Some tiype of internal app id that matches the app id configured on the server
	postString = [postString stringByAppendingString:@"&device_identifier="];
	postString = [postString stringByAppendingString:deviceIdentifier];
	postString = [postString stringByAppendingString:@"&device_token="];
	postString = [postString stringByAppendingString:deviceToken];
	postString = [postString stringByAppendingString:@"&device_name="];
	postString = [postString stringByAppendingString:deviceName];
	postString = [postString stringByAppendingString:@"&device_model="];
	postString = [postString stringByAppendingString:deviceModel];
	postString = [postString stringByAppendingString:@"&device_version="];
	postString = [postString stringByAppendingString:deviceSystemVersion];
	postString = [postString stringByAppendingString:@"&badge_allowed="];
	postString = [postString stringByAppendingString:[NSNumber numberWithBool:pushBadge].intValue];
	postString = [postString stringByAppendingString:@"&alert_allowed="];
	postString = [postString stringByAppendingString:[NSNumber numberWithBool:pushAlert].intValue];
	postString = [postString stringByAppendingString:@"&sound_allowed="];
	postString = [postString stringByAppendingString:[NSNumber numberWithBool:pushSound].intValue]];

	// Register the Device Data
	// !!! CHANGE "http" TO "https" IF YOU ARE USING HTTPS PROTOCOL
	NSURL *url = [[NSURL alloc] initWithScheme:@"http" host:host path:@"/push/device/ios/register"];
    NSURLRequest *request = [[NSURLRequest alloc] initWithURL:url];
    [request setHTTPBody:[postString dataUsingEncoding:NSUTF8StringEncoding]];

	NSData *returnData = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
	NSLog(@"Register URL: %@", url);
	NSLog(@"Return Data: %@", returnData);

	#endif
}

/**
 * Failed to Register for Remote Notifications
 */
- (void)application:(UIApplication *)application didFailToRegisterForRemoteNotificationsWithError:(NSError *)error {

	#if !TARGET_IPHONE_SIMULATOR

	NSLog(@"Error in registration. Error: %@", error);

	#endif
}

```


### C) Register Action for Push
``` objective-c
/**
 * Remote Notification Received while application was open.
 */
- (void)application:(UIApplication *)application didReceiveRemoteNotification:(NSDictionary *)userInfo {

    #if !TARGET_IPHONE_SIMULATOR


    NSLog(@"remote notification: %@",[userInfo description]);
    NSDictionary *apsInfo = [userInfo objectForKey:@"aps"];

    NSString *alert = [apsInfo objectForKey:@"alert"];
    NSLog(@"Received Push Alert: %@", alert);

    NSString *sound = [apsInfo objectForKey:@"sound"];
    NSLog(@"Received Push Sound: %@", sound);
    AudioServicesPlaySystemSound(kSystemSoundID_Vibrate);

    NSString *badge = [apsInfo objectForKey:@"badge"];
    NSLog(@"Received Push Badge: %@", badge);
    application.applicationIconBadgeNumber = [[apsInfo objectForKey:@"badge"] integerValue];

    #endif
}
```

[Step 6: Integration with Android](6-integration_with_android.md).

