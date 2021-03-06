//
//  InfectedKeysFormData.swift
//  TracePrivately
//

import UIKit

protocol InfectedKeysFormDataField {
    var name: String { get }
    var requestJson: [String: Any]? { get }
    
    var isValid: Bool { get }
}

struct InfectedKeysFormDataStringField: InfectedKeysFormDataField {
    let name: String
    let value: String
    
    var requestJson: [String : Any]? {
        return [
            "name": self.name,
            "type": "string",
            "str": self.value
        ]
    }
    
    var isValid: Bool {
        return true
    }
}

// TODO: Ensure resized to an adequate size
struct InfectedKeysFormDataImageField: InfectedKeysFormDataField {
    let name: String
    let image: UIImage
    
    var requestJson: [String : Any]? {
        guard let pngData = self.image.pngData() else {
            return nil
        }
        
        return [
            "name": self.name,
            "type": "image/png",
            "str": pngData.base64EncodedString(),
        ]
    }
    
    var isValid: Bool {
        return true
    }
}

struct InfectedKeysFormData {
    
    let fields: [InfectedKeysFormDataField]
    
    /// Returns an array of data that can be encoded to JSON
    var requestJson: [[String: Any]] {
        return self.fields.compactMap { $0.requestJson }
    }
}
