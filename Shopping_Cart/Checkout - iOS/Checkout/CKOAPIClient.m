//
//  CKOAPIClient.m
//  Checkout
//
//  Created by Zhe Wang on 9/2/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//

#import <CommonCrypto/CommonDigest.h>
#import <objc/runtime.h>
#if TARGET_OS_IPHONE
#import <UIKit/UIKit.h>
#import <sys/utsname.h>
#endif

#import "CKOAPIClient.h"
#import "CKOCard.h"
#import "CKOToken.h"
#import "CheckoutError.h"

static NSString *const apiURLBase = @"api2.checkout.com";
static NSString *const apiVersion = @"v1";
static NSString *const tokenEndpoint = @"tokens/card";
static NSString *CKODefaultPublishableKey;
static char kAssociatedClientKey;


@implementation Checkout

+ (void)setDefaultPublishableKey:(NSString *)publishableKey {
    CKODefaultPublishableKey = publishableKey;
}

+ (NSString *)defaultPublishableKey {
    return CKODefaultPublishableKey;
}

@end

typedef void (^CKOAPIConnectionCompletionBlock)(NSURLResponse *response, NSData *body, NSError *requestError);

// Like NSURLConnection
@interface CKOAPIConnection : NSObject<NSURLConnectionDelegate, NSURLConnectionDataDelegate>

- (instancetype)initWithRequest:(NSURLRequest *)request;
- (void)runOnOperationQueue:(NSOperationQueue *)queue completion:(CKOAPIConnectionCompletionBlock)handler;

@property (nonatomic) BOOL started;
@property (nonatomic, copy) NSURLRequest *request;
@property (nonatomic, strong) NSURLConnection *connection;
@property (nonatomic, strong) NSMutableData *receivedData;
@property (nonatomic, strong) NSURLResponse *receivedResponse;
@property (nonatomic, strong) NSError *overrideError; // Replaces the request's error
@property (nonatomic, copy) CKOAPIConnectionCompletionBlock completionBlock;

@end

@interface CKOAPIClient ()
@property (nonatomic, readwrite) NSURL *apiURL;
@end

@implementation CKOAPIClient

+ (instancetype)sharedClient {
    static id sharedClient;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{ sharedClient = [[self alloc] init]; });
    return sharedClient;
}

- (instancetype)init {
    return [self initWithPublishableKey:[Checkout defaultPublishableKey]];
}

- (instancetype)initWithPublishableKey:(NSString *)publishableKey {
    self = [super init];
    if (self) {
        [self.class validateKey:publishableKey];
        _apiURL = [[[NSURL URLWithString:[NSString stringWithFormat:@"https://%@", apiURLBase]] URLByAppendingPathComponent:apiVersion]
                   URLByAppendingPathComponent:tokenEndpoint];
        _publishableKey = [publishableKey copy];
        _operationQueue = [NSOperationQueue mainQueue];
    }
    return self;
}

- (void)setOperationQueue:(NSOperationQueue *)operationQueue {
    NSCAssert(operationQueue, @"Operation queue cannot be nil.");
    _operationQueue = operationQueue;
}

#pragma mark - private helpers

#pragma clang diagnostic push
#pragma clang diagnostic ignored "-Wunused-variable"
+ (void)validateKey:(NSString *)publishableKey {
    NSCAssert(publishableKey != nil && ![publishableKey isEqualToString:@""],
              @"You must use a valid publishable key to create a token.");
    BOOL secretKey = [publishableKey hasPrefix:@"sk_"];
    NSCAssert(!secretKey,
              @"You are using a secret key to create a token, instead of the publishable one.");
#ifndef DEBUG
    if ([publishableKey.lowercaseString hasPrefix:@"pk_test"]) {
        NSLog(@"⚠️ Warning! You're building your app in a non-debug configuration, but appear to be using your Checkout test key. Make sure not to submit to "
              @"the App Store with your test keys!⚠️");
    }
#endif
}


#pragma clang diagnostic pop

+ (NSError *)errorFromCheckoutResponse:(NSDictionary *)jsonDictionary {
    
    // Error from Checkout Response can be categorized into three categories
    
    // 1. Validation Error - 400 HTTP Response Code
    NSString *errorCode = jsonDictionary[@"errorCode"];
    NSString *message = jsonDictionary[@"message"];
    NSString *errors = jsonDictionary[@"errors"];
    
    // 2. Transaction Error - 402 HTTP Response Code
    NSString *eventId = jsonDictionary[@"eventId"];
    

    if (errorCode != nil && message != nil && errors != nil) {
        NSDictionary *userInfo = @{
                                   NSLocalizedDescriptionKey: CKORequestValidationError,
                                   CKOErrorMessageKey: message
                                   };
        return [[NSError alloc] initWithDomain:CheckoutDomain code:CKOValidationError userInfo:userInfo];
    }
    
    if(errorCode != nil && message != nil &&eventId != nil){
        NSDictionary *userInfo = @{
                                   NSLocalizedDescriptionKey: CKOTransactionProcessingError,
                                   CKOErrorMessageKey: message
                                   };
        return [[NSError alloc] initWithDomain:CheckoutDomain code:CKOTransactionError userInfo:userInfo];
    }
    
    // 3. The response code returned does not start with 100
    NSString *responseCode = jsonDictionary[@"responsecode"];
    NSString *responseMessage = jsonDictionary[@"responsemessage"];
    
    NSDictionary *userInfo;
    
        //check response code
    if (responseCode!= nil && ![responseCode hasPrefix:@"10"]){
        userInfo = @{
                    NSLocalizedDescriptionKey: CKOCardErrorDeclinedUserMessage,
                    CKOErrorMessageKey: responseMessage
                    };
        
    }


    return [[NSError alloc] initWithDomain:CheckoutDomain code:CKOAPIError userInfo:userInfo];
    
    
    
}

#pragma mark Utility methods -

+ (NSDictionary *)checkoutUserAgentDetails {
    NSMutableDictionary *details = [@{
                                      @"lang": @"objective-c",
                                      @"bindings_version": STPSDKVersion,
                                      } mutableCopy];
#if TARGET_OS_IPHONE
    NSString *version = [UIDevice currentDevice].systemVersion;
    if (version) {
        details[@"os_version"] = version;
    }
    struct utsname systemInfo;
    uname(&systemInfo);
    NSString *deviceType = @(systemInfo.machine);
    if (deviceType) {
        details[@"type"] = deviceType;
    }
    NSString *model = [UIDevice currentDevice].localizedModel;
    if (model) {
        details[@"model"] = model;
    }
    if ([[UIDevice currentDevice] respondsToSelector:@selector(identifierForVendor)]) {
        NSString *vendorIdentifier = [[[UIDevice currentDevice] performSelector:@selector(identifierForVendor)] performSelector:@selector(UUIDString)];
        if (vendorIdentifier) {
            details[@"vendor_identifier"] = vendorIdentifier;
        }
    }
#endif
    return [details copy];
}

+ (NSString *)JSONStringForObject:(id)object {
    return [[NSString alloc] initWithData:[NSJSONSerialization dataWithJSONObject:object options:0 error:NULL] encoding:NSUTF8StringEncoding];
}

@end

@implementation CKOAPIClient(CreditCards)

- (void)createTokenWithCard:(CKOCard *)card completion:(CKOCompletionBlock)completion{
    [self createTokenWithData:[self.class formEncodedDataForCard:card] completion:completion];
}

@end

@implementation CKOAPIClient(PrivateMethods)

- (void)createTokenWithData:(NSData *)data completion:(CKOCompletionBlock)completion {
    NSCAssert(data != nil, @"'data' is required to create a token");
    NSCAssert(completion != nil, @"'completion' is required to use the token that is created");
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:self.apiURL];
    request.HTTPMethod = @"POST";
    
    request.HTTPBody = data;
    [request setValue:[self.class JSONStringForObject:[self.class checkoutUserAgentDetails]] forHTTPHeaderField:@"X-Checkout-User-Agent"];
    [request setValue:[@"Bearer " stringByAppendingString:self.publishableKey] forHTTPHeaderField:@"Authorization"];
    
    CKOAPIConnection *connection = [[CKOAPIConnection alloc] initWithRequest:request];
    
    // use the runtime to ensure we're not dealloc'ed before completion
    objc_setAssociatedObject(connection, &kAssociatedClientKey, self, OBJC_ASSOCIATION_RETAIN);
    
    [connection runOnOperationQueue:self.operationQueue
                         completion:^(NSURLResponse *response, NSData *body, NSError *requestError) {
                             if (requestError) {
                                 // If this is an error that Stripe returned, let's handle it as a StripeDomain error
                                 if (body) {
                                     NSDictionary *jsonDictionary = [NSJSONSerialization JSONObjectWithData:body options:0 error:NULL];
                                     if ([jsonDictionary valueForKey:@"errorCode"] != nil || ![[jsonDictionary valueForKey:@"responsecode"] hasPrefix:@"10"]) {
                                         completion(nil, [self.class errorFromCheckoutResponse:jsonDictionary]);
                                         return;
                                     }
                                 }
                                 completion(nil, requestError);
                                 return;
                             } else {
                                 NSDictionary *jsonDictionary = [NSJSONSerialization JSONObjectWithData:body options:0 error:NULL];
                                 if (!jsonDictionary) {
                                     NSDictionary *userInfo = @{
                                                                NSLocalizedDescriptionKey: CKOUnexpectedError,
                                                                CKOErrorMessageKey: @"The response from Checkout failed to get parsed into valid JSON."
                                                                };
                                     NSError *error = [[NSError alloc] initWithDomain:CheckoutDomain code:CKOAPIError userInfo:userInfo];
                                     completion(nil, error);
                                 } else if ([(NSHTTPURLResponse *)response statusCode] == 200) {
                                     completion([[CKOToken alloc] initWithAttributeDictionary:jsonDictionary], nil);
                                 } else {
                                     completion(nil, [self.class errorFromCheckoutResponse:jsonDictionary]);
                                 }
                             }
                             // at this point it's safe to be dealloced
                             objc_setAssociatedObject(connection, &kAssociatedClientKey, nil, OBJC_ASSOCIATION_RETAIN);
                         }];
    
    
}

+ (NSData *)formEncodedDataForCard:(CKOCard *)card {
    NSCAssert(card != nil, @"Cannot create a token with a nil card.");
    NSMutableDictionary *params = [NSMutableDictionary dictionary];
    
    if (card.number) {
        params[@"number"] = card.number;
    }
    if (card.cvv) {
        params[@"cvv"] = card.cvv;
    }
    if (card.name) {
        params[@"name"] = card.name;
    }
    if (card.addressLine1) {
        params[@"billingdetails"][@"addressline1"] = card.addressLine1;
    }
    if (card.addressLine2) {
        params[@"billingdetails"][@"addressline2"] = card.addressLine2;
    }
    if (card.city) {
        params[@"billingdetails"][@"city"] = card.city;
    }
    if (card.state) {
        params[@"billingdetails"][@"state"] = card.state;
    }
    if (card.addressPostcode) {
        params[@"billingdetails"][@"postcode"] = card.addressPostcode;
    }
    if (card.country) {
        params[@"billingdetails"][@"country"] = card.country;
    }
    if (card.expiryMonth) {
        params[@"expirymonth"] = @(card.expiryMonth).stringValue;
    }
    if (card.expiryYear) {
        params[@"expiryyear"] = @(card.expiryYear).stringValue;
    }
    
    NSMutableArray *parts = [NSMutableArray array];
    
    [params enumerateKeysAndObjectsUsingBlock:^(id key, id val, __unused BOOL *stop) {
        [parts addObject:[NSString stringWithFormat:@"card[%@]=%@", key, [self.class stringByURLEncoding:val]]];
        
    }];
    
    return [[parts componentsJoinedByString:@"&"] dataUsingEncoding:NSUTF8StringEncoding];
}

+ (NSString *)stringByURLEncoding:(NSString *)string {
    NSMutableString *output = [NSMutableString string];
    const unsigned char *source = (const unsigned char *)[string UTF8String];
    NSInteger sourceLen = strlen((const char *)source);
    for (int i = 0; i < sourceLen; ++i) {
        const unsigned char thisChar = source[i];
        if (thisChar == ' ') {
            [output appendString:@"+"];
        } else if (thisChar == '.' || thisChar == '-' || thisChar == '_' || thisChar == '~' || (thisChar >= 'a' && thisChar <= 'z') ||
                   (thisChar >= 'A' && thisChar <= 'Z') || (thisChar >= '0' && thisChar <= '9')) {
            [output appendFormat:@"%c", thisChar];
        } else {
            [output appendFormat:@"%%%02X", thisChar];
        }
    }
    return output;
}

+ (NSString *)stringByReplacingSnakeCaseWithCamelCase:(NSString *)input {
    NSArray *parts = [input componentsSeparatedByString:@"_"];
    NSMutableString *camelCaseParam = [NSMutableString string];
    [parts enumerateObjectsUsingBlock:^(NSString *part, NSUInteger idx, __unused BOOL *stop) {
        [camelCaseParam appendString:(idx == 0 ? part : [part capitalizedString])];
    }];
    
    return [camelCaseParam copy];
}

@end

@implementation CKOAPIConnection

- (instancetype)initWithRequest:(NSURLRequest *)request {
    if (self = [super init]) {
        _request = request;
        _connection = [[NSURLConnection alloc] initWithRequest:_request delegate:self startImmediately:NO];
        _receivedData = [[NSMutableData alloc] init];
    }
    return self;
}

- (void)runOnOperationQueue:(NSOperationQueue *)queue completion:(CKOAPIConnectionCompletionBlock)handler {
    NSCAssert(!self.started, @"This API connection has already started.");
    NSCAssert(queue, @"'queue' is required");
    NSCAssert(handler, @"'handler' is required");
    
    self.started = YES;
    self.completionBlock = handler;
    [self.connection setDelegateQueue:queue];
    [self.connection start];
}

#pragma mark NSURLConnectionDataDelegate

- (void)connection:(__unused NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response {
    self.receivedResponse = response;
}

- (void)connection:(__unused NSURLConnection *)connection didReceiveData:(NSData *)data {
    [self.receivedData appendData:data];
}

- (void)connectionDidFinishLoading:(__unused NSURLConnection *)connection {
    self.connection = nil;
    self.completionBlock(self.receivedResponse, self.receivedData, nil);
    self.receivedData = nil;
    self.receivedResponse = nil;
}

@end