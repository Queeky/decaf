function getRand(min, max) {
    min++; 
    max++; 
    return Math.floor(Math.random() * (max - min) + min);
}

function getScheme(lodestone) {
    const index = getRand(0, 2); // Index of color that will remain the same
    let newColor = []; 

    for (const color of lodestone) {
        if (color == lodestone[index]) {
            newColor.push(color); 
        } else {
            const op = getRand(0, 1); 
            let value = getRand(30, 100); 

            switch(color) {
                case color >= 100 && color <= 155: 
                    value = op ? value : (value * -1); 
                    break; 
                case color >= 100: 
                    value = value * -1; 
            }

            newColor.push(color + value); 
        }
    }

    return `rgb(${newColor[0]}, ${newColor[1]}, ${newColor[2]})`; 
}

async function generateGarden() {
    let squares = []; 

    const amount = getRand(100, 2000); 
    const lodestone = [getRand(0, 255), getRand(0, 255), getRand(0, 255)];

    for (let i = 0; i < amount; i++) {

        const square = {
            x: getRand(0, 95), 
            y: getRand(0, 95), 
            size: getRand(5, 50) * 0.02, 
            color: getScheme(lodestone)
        }

        squares.push(square); 
    } 

    const squaresString = JSON.stringify(squares); 
    console.log(squaresString); 
    sessionStorage.setItem("pixelGarden", squaresString); 
}

async function showGarden(squares) {
    const parent = document.querySelector(".garden-wrapper"); 

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

if (!sessionStorage.getItem("pixelGarden")) {
    console.log("unset"); 
    generateGarden(); 
} 

let squares = sessionStorage.getItem("pixelGarden");
squares = JSON.parse(squares); 

console.log(squares); 

showGarden(squares); 