Step 7: Integration With Safari (Requires FOSUserBundle or Some Type Of User System)
=======================================


### A)  Register For Notifications:

Add the following bit of code replacing the values as necessary after a user has logged into your website.

``` javascript

document.body.onload = function() {
                // Ensure that the user can receive Safari Push Notifications.
                if ('safari' in window && 'pushNotification' in window.safari) {
                    console.log("Can do push");
                    var permissionData = window.safari.pushNotification.permission('{{ website_push_id }}');
                    checkRemotePermission(permissionData);
                } else {
                    console.log("Can't do push");
                }
            };

            var checkRemotePermission = function (permissionData) {
                console.log(permissionData);
                if (permissionData.permission === 'default') {
                    // This is a new web service URL and its validity is unknown.
                    console.log("Asking");
                    window.safari.pushNotification.requestPermission(
                            '{{ base_url }}', // The web service URL.
                            '{{ website_push_id }}',     // The Website Push ID.
                            {userId:'{{ app.user.id }}'}, // Data that you choose to send to your server to help you identify the user.
                            checkRemotePermission         // The callback function.
                    );
                } else if (permissionData.permission === 'denied') {
                    // The user said no.
                    console.log("No");

                }else if (permissionData.permission === 'granted') {
                    // The web service URL is a valid push provider, and the user said yes.
                    // permissionData.deviceToken is now available to use.
                    //Here we resend the registration request incase the the user identity changed.
                     console.log("Yes");
                    $.ajax({
                        type:"POST",
                        beforeSend: function (request)
                        {
                            request.setRequestHeader("Authorization", "ApplePushNotifications authenticationToken_{{ app.user.id }}");
                        },
                        url: "{{ base_url }}" + "/v1/devices/" + permissionData.deviceToken + "/registrations/" + "{{ website_push_id }}",
                        processData: false
                    });
                }
            };

```

Once that is in the webservices routing should cover the rest. Make sure you imported the safari routing files.