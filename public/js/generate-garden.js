function getRand(min, max) {
    max++; 
    return Math.floor(Math.random() * (max - min) + min);
}

function setCoords(n, magicNum) {
    const distance = getRand(50, magicNum + 10); 

    if ((n + distance < 95) && (n - distance > 0)) return getRand(0, 1) ? n + distance : n - distance; 
    if ((n + distance > 95) && (n - distance > 0)) return n - distance; 
    return getRand(0, 95); // Yeet it somewhere
}

function getScheme(rgb) {
    const index = getRand(0, 2); // Index of color that will remain the same
    let newColor = []; 

    for (const color of rgb) {
        if (color == rgb[index]) {
            newColor.push(color); 
        } else {
            let value = getRand(30, 100); 

            if (color >= 100 && color <= 155) {
                value = getRand(0, 1) ? value : (value * -1);
            } else if (color >= 100) {
                value = value * -1; 
            }

            newColor.push(color + value); 
        }
    }

    return `rgb(${newColor[0]}, ${newColor[1]}, ${newColor[2]})`; 
}

async function generateGarden() {
    let squares = []; 
    let key = "";  

    const primary = [getRand(0, 255), getRand(0, 255), getRand(0, 255)];
    const secondary = [getRand(0, 255), getRand(0, 255), getRand(0, 255)];

    const magicNum = Math.abs((primary[0] + primary[1] + primary[2]) - (secondary[0] + secondary[1] + secondary[2])); 

    const amount = (magicNum * 5 < 750) ? magicNum * 17 : magicNum * 9; 

    const background = primary.map(item => {
        if (item < 186) return item + 70; 
        return item - 70; 
    });

    const backgroundString = `rgb(${background[0]}, ${background[1]}, ${background[2]})`; 
    sessionStorage.setItem("gardenBackground", backgroundString); 

    const hotspotCount = Math.ceil(magicNum / 30); // Generates hotspots for squares to congregate around
    const distance = Math.ceil(magicNum / 10); 
    let hotspots = []; 
    for (let i = 0; i < hotspotCount; i++) {
        hotspots.push({x: getRand(0, 95), y: getRand(0, 95)}); 
    }

    for (let i = 0; i < amount; i++) {
        const index = getRand(0, hotspots.length - 1); 
        const hotspot = hotspots[index]; // Selecting a hotspot

        const x = setCoords(hotspot.x, distance); 
        const y = setCoords(hotspot.y, distance); 

        key += `${x + y}`; 

        const colorSelect = getRand(0, 1); // 1 = primary, 0 = secondary
        const color = colorSelect ? primary : secondary; 

        const square = {
            x: x, 
            y: y, 
            size: getRand(7, 45) * 0.02, 
            color: getScheme(color)
        }

        squares.push(square); 
    }

    document.cookie = `key=${key}`; 
    // console.log("KEY -->", key); 

    const squaresString = JSON.stringify(squares); 
    sessionStorage.setItem("pixelGarden", squaresString); 
}

async function showGarden(squares) {
    const parent = document.querySelector(".garden-wrapper"); 
    const innerContent = document.querySelector(".inner-content.garden");

    innerContent.style.backgroundColor = sessionStorage.getItem("gardenBackground"); 

    for (const square of squares) {
        const squareDOM = document.createElement("div"); 
        squareDOM.className = "garden-square"; 
        squareDOM.style.cssText = `
            display: inline-block; 
            width: ${square.size}vw;
            height: ${square.size}vw;
            background-color: ${square.color}; 
            position: absolute; 
            left: ${square.x}%; 
            top: ${square.y}%; 
        `; 

        parent.appendChild(squareDOM); 
    }
}

if (!sessionStorage.getItem("pixelGarden")) generateGarden(); 

let squares = sessionStorage.getItem("pixelGarden");
squares = JSON.parse(squares); 

showGarden(squares); 