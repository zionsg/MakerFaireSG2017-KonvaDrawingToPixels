<?php
$config = include 'config/config.php';
$cellWidth = $config['cell_width'];
$cellsPerRow = $config['cells_per_row'];
$cellsPerColumn = $config['cells_per_column'];
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <!-- Meta data -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Title -->
    <title>Konva Drawing to Pixels - MakerFaire SG 2017</title>

    <!-- Styles -->
    <link rel="stylesheet" href="public/css/global.css">
  </head>

  <body>
    <div id="tools">
      <a id="submit" href="#">&check;</a>
      <a id="clear" href="#">&cross;</a>
    </div>

    <div id='stage'></div>

    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="node_modules/konva/konva.min.js"></script>
    <script src="public/js/utils.js"></script>
    <script>
      $(function () {
          var cellWidth = <?php echo $cellWidth; ?>;
          var cellsPerRow = <?php echo $cellsPerRow; ?>;
          var cellsPerColumn = <?php echo $cellsPerColumn; ?>;
          var canvasWidth = cellsPerRow * cellWidth;
          var canvasHeight = cellsPerColumn * cellWidth;
          var cells = [];
          var cellCnt = 0;
          var initialColor = '#c0c0c0';
          var currColor = '#0000ff';
          var cell;

          // Setup tools
          var $tools = $('#tools');
          $.each(['#ff0000', '#ffff00', '#00ff00', '#00ffff', '#0000ff', '#ff00ff', '#000000', '#ffffff'], function () {
              $tools.append(utils.sprintf(
                  '<a href="#" class="tool" data-color="%s" style="background-color:%s;">&nbsp;</a> ',
                  this,
                  this
              ));
          });
          $('.tool').click(function (e) {
              e.preventDefault();
              currColor = $(this).attr('data-color');
          });
          $('#submit').click(function (e) {
              e.preventDefault();

              var data = [];
              for (var row = 0; row < cellsPerColumn; row++) {
                  var set = [];

                  for (var col = 0; col < cellsPerRow; col++) {
                      cell = cells[(row * cellsPerRow) + col];
                      set.push(cell.konvaCell.getFill());
                  }

                  data.push(set);
              }

              utils.sendDrawing(data, function (isSuccess, statusCode, responseData) {
                  alert('Drawing sent!');
              });
          });
          $('#clear').click(function (e) {
              e.preventDefault();

              for (var i = 0; i < cellCnt; i++) {
                  cells[i].konvaCell.fill('#c0c0c0');
              }
              layer.draw();
          });

          // Setup stage
          var canvasX = 0;
          var canvasY = 0;
          var stage = new Konva.Stage({
              container: 'stage',
              width: canvasWidth,
              height: canvasHeight
          });
          var layer = new Konva.Layer();
          var canvas = new Konva.Rect({
              x: canvasX,
              y: canvasY,
              width: canvasWidth,
              height: canvasHeight,
              fill: 'transparent',
              stroke: 'black',
              strokeWidth: 1
          });

          // Create cells
          var cellX, cellY;
          for (var row = 0; row < cellsPerColumn; row++) {
              for (var col = 0; col < cellsPerRow; col++) {
                  // Add cell to layer and save coords
                  cellX = (col * cellWidth);
                  cellY = (row * cellWidth);
                  var cell = new Konva.Rect({
                      x: cellX,
                      y: cellY,
                      width: cellWidth,
                      height: cellWidth,
                      fill: initialColor,
                      stroke: 'black',
                      strokeWidth: 1
                  });

                  layer.add(cell);
                  cells.push({
                      konvaCell: cell,
                      xMin: cellX,
                      xMax: cellX + cellWidth,
                      yMin: cellY,
                      yMax: cellY + cellWidth
                  });
                  cellCnt++;
              }
          }

          // For performance, adding 2 event listeners on 1 element rather than 2 listeners for each cell
          canvas.on('touchstart touchmove', function () {
              var touchPos = stage.getPointerPosition();
              var x = touchPos.x;
              var y = touchPos.y;

              // Instead of looping thru all the cells, use approximation to find the cell
              row = Math.floor(y / cellWidth);
              col = Math.floor(x / cellWidth);
              cell = cells[(row * cellsPerRow) + col];

              // Color cell
              cell.konvaCell.fill(currColor);
              layer.draw();
          });

          // Canvas must be added to layer only after touch event is added
          layer.add(canvas);

          // Finally, add layer to stage
          stage.add(layer);
      });
    </script>
  </body>
</html>
