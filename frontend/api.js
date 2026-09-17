
if(product.reorder_level >= product.quantity){
    "good"
}else if(product.reorder_level < product.quantity){
    "low"
}else{
    "out"
}