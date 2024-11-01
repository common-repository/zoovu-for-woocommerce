const pluck = (objs, name) => {
    var sol = [];
    for(var i in objs){
        if(objs[i].hasOwnProperty(name)){
            // console.log(objs[i][name]);
            sol.push(objs[i][name]);
        }
    }
    return sol;
}

export default pluck;