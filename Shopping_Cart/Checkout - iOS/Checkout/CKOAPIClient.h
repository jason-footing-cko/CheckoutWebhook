//
//  CKOAPIClient.h
//  Checkout
//
//  Created by Zhe Wang on 9/2/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//



#import <Foundation/Foundation.h>

static NSString *const STPSDKVersion = @"1.0.0";

@class CKOCard, CKOToken;

/**
 *  A callback to be run with the response from the Checkout API.
 *
 *  @param token The Checkout token from the response. Will be nil if an error occurs. @see CKOToken
 *  @param error The error returned from the response, or nil in one occurs. @see CheckoutError.h for possible values.
 */
typedef void (^CKOCompletionBlock)(CKOToken *token, NSError *error);

/**
 A top-level class that imports the rest of the Checkout SDK.
 */
@interface Checkout : NSObject

/**
 *  Set your Checkout API key with this method. New instances of CKOAPIClient will be initialized with this value. You should call this method as early as
 *  possible in your application's lifecycle, preferably in your AppDelegate.
 *
 *  @param   publishableKey Your publishable key, obtained from https://manage.checkout.com
 *  @warning Make sure not to ship your test API keys to the App Store! This will log a warning if you use your test key in a release build.
 */
+ (void)setDefaultPublishableKey:(NSString *)publishableKey;

/// The current default publishable key.
+ (NSString *)defaultPublishableKey;
@end

/// A client for making connections to the Stripe API.
@interface CKOAPIClient: NSObject

/**
 *  A shared singleton API client. Its API key will be initially equal to [Checkout defaultPublishableKey].
 */
+ (instancetype)sharedClient;
- (instancetype)initWithPublishableKey:(NSString *)publishableKey NS_DESIGNATED_INITIALIZER;


/**
 *  @see [Checkout setDefaultPublishableKey:]
 */
@property (nonatomic, copy) NSString *publishableKey;

/**
 *  The operation queue on which to run the url connection and delegate methods. Cannot be nil. @see NSURLConnection
 */
@property (nonatomic) NSOperationQueue *operationQueue;

@end


#pragma mark - Credit Cards

@interface CKOAPIClient (CreditCards)

/**
 *  Converts an CKOCard object into a Checkout token using the Checkout API.
 *
 *  @param card        The user's card details. Cannot be nil. @see https://dev.checkout.com/ref/apiwebsite/#create-card-token
 *  @param completion  The callback to run with the returned Checkout token (and any errors that may have occurred).
 */
- (void)createTokenWithCard:(CKOCard *)card completion:(CKOCompletionBlock)completion;

@end

@interface CKOAPIClient (PrivateMethod)

- (void)createTokenWithData:(NSData *)data completion:(CKOCompletionBlock)completion;

+ (NSData *)formEncodedDataForCard:(CKOCard *)card;

+ (NSString *)stringByURLEncoding:(NSString *)string;

+ (NSString *)stringByReplacingSnakeCaseWithCamelCase:(NSString *)input;

@end
