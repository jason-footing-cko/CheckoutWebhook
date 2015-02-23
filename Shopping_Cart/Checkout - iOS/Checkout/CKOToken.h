//
//  CKOToken.h
//  Checkout
//
//  Created by Zhe Wang on 22/1/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//

#import <Foundation/Foundation.h>

@class CKOCard;

/**
 
* A token returned from submitting payment details to the Checkout API. You should not instantiate one of these directly

*/

@interface CKOToken : NSObject

/**
 
 * The ID of the token. You can store this value on your server and use it for the charge
 * Details: http://dev.checkout.com/ref/apiwebsite/#charge-with-card-token
 
 */

@property(nonatomic, readonly) NSString *tokenID;

/*
 
 * The token is created under Live Mode or Test Mode. It will be YES if it is created using Live Publishable Key and No if you use Test Publishable Key
 */

@property (nonatomic, readonly) BOOL liveMode;


/*
 
 * The card details that were used to crate the token. will only be set if the token was created via validate cards, otherwise it will be nil
 */

@property(nonatomic, readonly) CKOCard *card;


/*
 
 * The UNIX timestamp representing when the token was created
 
 */

@property(nonatomic, readonly) NSDate *created;

typedef void (^CKOCardServerResponseCallback)(NSURLResponse *response, NSData *data, NSError *error);

/**
 *  Form-encode the token and post those parameters to your backend URL.
 *
 *  @param url     the URL to upload the token details to
 *  @param params  optional parameters to additionally include in the POST body
 *  @param handler code to execute with your server's response
 */

- (void)postToURL:(NSURL *)url withParams:(NSDictionary *)params completion:(CKOCardServerResponseCallback)handler;


@end


@interface CKOToken (PrivateMethods)

- (instancetype)initWithAttributeDictionary:(NSDictionary *)attributeDictionary;

@end