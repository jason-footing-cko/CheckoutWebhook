//
//  CKOToken.m
//  Checkout
//
//  Created by Zhe Wang on 27/1/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//

#import "CKOToken.h"
#import "CKOCard.h"


@implementation CKOToken

- (instancetype)initWithAttributeDictionary:(NSDictionary *)attributeDictionary {
    self = [super init];
    
    if (self) {
        _tokenID = attributeDictionary[@"id"];
        _liveMode = [attributeDictionary[@"liveMode"] boolValue];
        _created = [NSDate dateWithTimeIntervalSince1970:[attributeDictionary[@"created"] doubleValue]];
        
        NSDictionary *cardDictionary = attributeDictionary[@"card"];
        if (cardDictionary) {
            _card = [[CKOCard alloc] initWithAttributeDictionary:cardDictionary];
        }
        
    }
    
    return self;
}


- (NSString *)description {
    NSString *token = self.tokenID ?: @"Unknown token";
    NSString *livemode = self.liveMode ? @"live mode" : @"test mode";
    
    return [NSString stringWithFormat:@"%@ (%@)", token, livemode];
}


- (void)postToURL:(NSURL *)url withParams:(NSMutableDictionary *)params completion:(CKOCardServerResponseCallback)handler {
    
    NSMutableString *body = [NSMutableString stringWithFormat:@"checkoutToken=%@", self.tokenID];
    
    [params enumerateKeysAndObjectsUsingBlock:^(id key, id obj, __unused BOOL *stop) { [body appendFormat:@"&%@=%@", key, obj]; }];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:url];
    request.HTTPMethod = @"POST";
    request.HTTPBody = [body dataUsingEncoding:NSUTF8StringEncoding];
    
    [NSURLConnection sendAsynchronousRequest:request queue:[NSOperationQueue mainQueue] completionHandler:handler];
}

@end
