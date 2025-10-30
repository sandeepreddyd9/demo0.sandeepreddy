// Static Products Data - Easy to Edit
// Add your products here - no database needed!

const staticProducts = [
    // VEGETABLES
    {
        id: 1,
        name: 'Organic Tomatoes',
        description: 'Fresh, juicy organic tomatoes perfect for salads and cooking. Rich in lycopene and vitamin C.',
        price: 80.00,
        category: 'vegetables',
        stock_quantity: 50,
        rating: 4.5,
        farmer_info: 'Green Valley Farm',
        image_url: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500&h=500&fit=crop&crop=center&auto=format&q=80'
    },
    {
        id: 2,
        name: 'Organic Carrots',
        description: 'Sweet and crunchy organic carrots, rich in beta-carotene and perfect for snacking.',
        price: 60.00,
        category: 'vegetables',
        stock_quantity: 40,
        rating: 4.7,
        farmer_info: 'Sunny Acres',
        image_url: 'https://images.unsplash.com/photo-1445282768818-728615cc910a?w=500&h=500&fit=crop&crop=center&auto=format&q=80'
    },
    {
        id: 3,
        name: 'Organic Spinach',
        description: 'Fresh organic spinach leaves, packed with iron, vitamins, and minerals.',
        price: 45.00,
        category: 'vegetables',
        stock_quantity: 30,
        rating: 4.3,
        farmer_info: 'Leafy Greens Co.',
        image_url: 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 4,
        name: 'Organic Bell Peppers',
        description: 'Colorful organic bell peppers, sweet and crispy, perfect for cooking.',
        price: 120.00,
        category: 'vegetables',
        stock_quantity: 25,
        rating: 4.6,
        farmer_info: 'Rainbow Farms',
        image_url: 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 5,
        name: 'Organic Broccoli',
        description: 'Fresh organic broccoli crowns, rich in vitamins K, C, and folate.',
        price: 90.00,
        category: 'vegetables',
        stock_quantity: 20,
        rating: 4.4,
        farmer_info: 'Green Valley Farm',
        image_url: 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=400&h=400&fit=crop&crop=center'
    },

    // FRUITS
    {
        id: 6,
        name: 'Organic Apples',
        description: 'Crisp and sweet organic apples, perfect for snacking and rich in fiber.',
        price: 150.00,
        category: 'fruits',
        stock_quantity: 60,
        rating: 4.8,
        farmer_info: 'Orchard Hills',
        image_url: 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 7,
        name: 'Organic Bananas',
        description: 'Fresh organic bananas, rich in potassium and natural sugars.',
        price: 70.00,
        category: 'fruits',
        stock_quantity: 80,
        rating: 4.4,
        farmer_info: 'Tropical Farms',
        image_url: 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 8,
        name: 'Organic Oranges',
        description: 'Juicy organic oranges packed with vitamin C and natural citrus flavor.',
        price: 100.00,
        category: 'fruits',
        stock_quantity: 45,
        rating: 4.5,
        farmer_info: 'Citrus Grove',
        image_url: 'https://images.unsplash.com/photo-1547514701-42782101795e?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 9,
        name: 'Organic Strawberries',
        description: 'Sweet and fragrant organic strawberries, perfect for desserts.',
        price: 200.00,
        category: 'fruits',
        stock_quantity: 15,
        rating: 4.9,
        farmer_info: 'Berry Best Farm',
        image_url: 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 10,
        name: 'Organic Grapes',
        description: 'Fresh organic grapes, perfect for snacking or making juice.',
        price: 180.00,
        category: 'fruits',
        stock_quantity: 25,
        rating: 4.6,
        farmer_info: 'Vineyard Valley',
        image_url: 'https://images.unsplash.com/photo-1537640538966-79f369143f8f?w=400&h=400&fit=crop&crop=center'
    },

    // GRAINS
    {
        id: 11,
        name: 'Organic Brown Rice',
        description: 'Nutritious organic brown rice, high in fiber and essential nutrients.',
        price: 120.00,
        category: 'grains',
        stock_quantity: 100,
        rating: 4.2,
        farmer_info: 'Golden Grain Co.',
        image_url: 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 12,
        name: 'Organic Quinoa',
        description: 'Premium organic quinoa, complete protein source and gluten-free superfood.',
        price: 300.00,
        category: 'grains',
        stock_quantity: 35,
        rating: 4.7,
        farmer_info: 'Ancient Grains Ltd.',
        image_url: 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 13,
        name: 'Organic Oats',
        description: 'Wholesome organic oats for a healthy breakfast, rich in beta-glucan.',
        price: 150.00,
        category: 'grains',
        stock_quantity: 50,
        rating: 4.3,
        farmer_info: 'Morning Harvest',
        image_url: 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=400&h=400&fit=crop&crop=center'
    },

    // DAIRY
    {
        id: 14,
        name: 'Organic Milk',
        description: 'Fresh organic milk from grass-fed cows, rich in calcium.',
        price: 80.00,
        category: 'dairy',
        stock_quantity: 40,
        rating: 4.6,
        farmer_info: 'Happy Cow Dairy',
        image_url: 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 15,
        name: 'Organic Cheese',
        description: 'Artisanal organic cheese, aged to perfection with rich flavor.',
        price: 250.00,
        category: 'dairy',
        stock_quantity: 20,
        rating: 4.8,
        farmer_info: 'Artisan Cheese Co.',
        image_url: 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 16,
        name: 'Organic Yogurt',
        description: 'Creamy organic yogurt with live cultures, perfect for digestive health.',
        price: 120.00,
        category: 'dairy',
        stock_quantity: 30,
        rating: 4.5,
        farmer_info: 'Pure Dairy Co.',
        image_url: 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&h=400&fit=crop&crop=center'
    },

    // HERBS
    {
        id: 17,
        name: 'Organic Basil',
        description: 'Fresh organic basil leaves, perfect for Italian cooking and aromatics.',
        price: 40.00,
        category: 'herbs',
        stock_quantity: 25,
        rating: 4.4,
        farmer_info: 'Herb Garden',
        image_url: 'https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 18,
        name: 'Organic Mint',
        description: 'Fresh organic mint leaves for teas, garnishes, and refreshing beverages.',
        price: 35.00,
        category: 'herbs',
        stock_quantity: 20,
        rating: 4.3,
        farmer_info: 'Fresh Herbs Inc.',
        image_url: 'https://images.unsplash.com/photo-1628556270448-4d4e4148e1b1?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 19,
        name: 'Organic Cilantro',
        description: 'Fresh organic cilantro for authentic flavors in Mexican and Asian cuisine.',
        price: 30.00,
        category: 'herbs',
        stock_quantity: 30,
        rating: 4.2,
        farmer_info: 'Spice Garden',
        image_url: 'https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=500&h=500&fit=crop&crop=center&auto=format&q=80'
    },

    // BEVERAGES
    {
        id: 20,
        name: 'Organic Green Tea',
        description: 'Premium organic green tea leaves, rich in antioxidants and natural energy.',
        price: 180.00,
        category: 'beverages',
        stock_quantity: 45,
        rating: 4.6,
        farmer_info: 'Mountain Tea Co.',
        image_url: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=400&fit=crop&crop=center'
    },
    {
        id: 21,
        name: 'Organic Apple Juice',
        description: 'Pure organic apple juice, no added sugars, made from fresh organic apples.',
        price: 120.00,
        category: 'beverages',
        stock_quantity: 25,
        rating: 4.4,
        farmer_info: 'Pure Juice Co.',
        image_url: 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=400&fit=crop&crop=center'
    },

    // OILS & SPICES
    {
        id: 22,
        name: 'Organic Olive Oil',
        description: 'Extra virgin organic olive oil, cold-pressed for maximum flavor and health benefits.',
        price: 350.00,
        category: 'oils',
        stock_quantity: 30,
        rating: 4.7,
        farmer_info: 'Mediterranean Oils',
        image_url: 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&h=400&fit=crop&crop=center'
    },

    // ADD MORE PRODUCTS HERE - Just copy the format above!
    /*
    {
        id: 23, // Make sure ID is unique
        name: 'Your Product Name',
        description: 'Your product description here',
        price: 99.00, // Price in rupees
        category: 'vegetables', // vegetables, fruits, grains, dairy, herbs, beverages, oils
        stock_quantity: 50,
        rating: 4.5, // Rating out of 5
        farmer_info: 'Your Farm Name',
        image_url: 'https://your-image-url-here.jpg'
    },
    */
];

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = staticProducts;
}
