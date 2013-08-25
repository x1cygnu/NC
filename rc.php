<?php

include_once("internal/html.php");

$H = new HTML();
$H->AddStyle("rc.css");

$H->sTitle="Piotr Danilewski: The Candle - Rendering Competition 2008";

$H->Insert("Piotr Danilewski (2520177)");
$H->Insert("<h1>The Candle</h1>");
$H->Insert(new Link("desc_main.png",new Image("desc_main_small.png"),"_blank"));
$H->Insert("<br>(all links will open in new window)");
$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"List of features");
$T->SetCols(3);
$T->Join(1,1,3,1);
$T->aRowClass[1]='title';
$T->Insert(1,2,"Feature");
$T->Insert(2,2,"Description");
$T->Insert(3,2,"Example");
$T->aRowClass[2]='legend';
$T->Insert(1,3,"Displacement<br>Mapping");
$T->SetClass(1,3,'sublegend');
$T->Insert(2,3,"It is the core element of my tracer as many other features depend on it.
It can be used both at global scale to shape the scene (i.e. terrain)
as well as at detail level to add realsim (i.e. candle surface).<br>
<br>
Main component of my displacement mapping is the HeightMap class.
It works similarly to a texture - it is a two-dimentional matrix of sampled positive height numbers. Arbitrary two dimentional coordinates of the heightmap may be applied to a vertex of a triangle primitive (HeightTriangle, SmoothHeightTriangle) pritty much the same as it is done with texture.<br>
HeightMap provides an intersect(Ray,minT,maxT) method to check if and where the given ray intersects the map. HeightMap has no information about spatial location of the triangle(s) it is used on, so the ray must be transformed into local reference system of the map. Map lies on XY surface and perturbation goes into Z direction. Whole array is spread over square [0..1]x[0..1] (later the ray is scaled internally). minT and maxT are the extreme points where ray is over the triangle we are intersecting. This saves time and increases quality - we do not want to intersect heightmap somewhere while not being over the triangle.<br>
Very naive implementation of HeightMap could be done by spawning all its triangles, putting them into kdtree and rendering by already written algorithms. This would actually spawn a great number of triangles. Displacement array of dimension 256 would generate at least 131072 triangles and would take a lot of time to compute even with good kd-tree. (For comparison - cow.obj uses less than 6000 triangles)<br>
My implementation of HeightMap takes advantage over spatial organisation of its triangles. It temporarly spawns four triangles only at the last step of the algorithm, when it localised the hit position there. Before that, my algorithm projects the ray over a grid and checks z coordinate at each point where x or y coordinate is integer (when unit distance is the distance between two height samples). The value is checked against linearly interpolated height from two closest grid points. Such check is very fast to compute in comparison to triangle intersection, and it is quick even without additional structuring of the matrix.<br>
Additional global value of max height is stored to avoid unnecessary checks. Also algorithm is terminated when we pass behind maxT.<br>
Special care is taken for rays incoming from perpendicular angle to the heightmap, when ray never intersects any integer coordinate and maxT is infinite.<br>
<br>
This approach is extreamly fast if incoming rays are nearly perpendicular to heightmap or maximal height is small - in that case only few comparison are being made before hitpoint is found. The algorithm is slower when maximal height is big but there are vast low regions and the ray is parallel to the surface. In that case intersection time is linear to the dimension of height array.<br>
This can be resolved by adding 2d quad-tree to the array. At each node the maximal height of its children would be stored which would hopefully be smaller than global maximal height. This way if height is big only at one or few positions, ray traversal elsewhere will be fast. I was attempting to implement the structure, but time was pressing on and there were still lots of bugs elsewhere, so I reverted back to the simpler - yet sufficently fast version.

Additionaly, heightmap interpolates gradient value at given 2D point, which is later used by HeightTriangle and SmoothHeightTriangle to compute perturbed normals. While HeightTriangle adds up vector to the gradient, SmoothHeightTriangle combines interpolated normal vector to it.<br>
SmoothHeightTriangle is used on the candle, everywhere else simpler HeightTriangle is being used.");
$T->SetClass(2,3,'q');
$T->Insert(3,3,"Displacement mapping can be seen everywhere:
Terrain, Candle, Table, Water.<br>");
$T->Insert(3,3,new Link("desc_displ.png","Frame 1054 - candle just before falling","_blank"));
$T->Insert(3,3,new Br());
$T->Insert(3,3,"On this frame it is probably seen the best,
how it interacts with world. The candle
is not just a straight cylinder, and it is
unevenly illuminated. Also note the shadow
of a mountain right from view point casted
across the table. The mountain is also
driven by the displacement map");
$T->SetClass(3,3,'q');
$T->Insert(1,4,"Fractal Geometry");
$T->SetClass(1,4,'sublegend');
$T->Insert(2,4,"Fractal geometry is used in two cases:<br>
Firstly it is used to generate terrain simply by applying 2d perlin noise to generate heightmap. The heightmap is generated at the beginning of program and then stored in raw format in memory.<br>
Secondly it is used in a candle. Basicly it uses perlin noise as well, but the heightmap is slightly streached and convolved with weighted motion blur to simulate frozen vax on the surface of the candle.<br>");
$T->Insert(3,4,"For candle - same as above<br>");
$T->Insert(3,4,new Link("desc_displ.png","Frame 1054 - candle just before falling","_blank"));
$T->Insert(3,4,new Br());
$T->Insert(3,4,"Terrain (not on the video):<br>");
$T->Insert(3,4,new Link("desc_terrain.png","Terrain map","_blank"));
$T->Insert(3,4,new Br());
$T->Insert(3,4,"This image shows terrain seen from top, with different lightning and replaced texture to emphasise height differences. Approximated camera path for first part of video is marked.");
$T->aRowClass[4]='q';
$T->Insert(1,5,"Water simulation<br>Reflective and Refractive Transparency");
$T->SetClass(1,5,"sublegend");
$T->Insert(2,5,"To simulate water surface a special WaterShader is created. For given incoming ray it generates two new rays, one for reflected component and one for refracted one (provided it exists). It uses Snell's law to compute refraction and Fresnel equations to weight the resulting colors. Additionaly, an additional attenuation for refracted ray is applied depending on the hit distance.<br>

Water surface uses small displacement map as well to generate tiny waves and make use of perturbed normals to add realism to water surface. It can be particularly seen by distortions of reflected objects.");
$T->Insert(3,5,new Link("desc_water.png","Frame 317 - wavy water","_blank"));
$T->Insert(3,5,new Br());
$T->Insert(3,5,new Link("desc_water_bright.png","Frame 317 with increased brightness","_blank"));
$T->Insert(3,5,new Br());
$T->Insert(3,5,new Link("desc_refract.png","Frame 220 - aproaching the pond","_blank"));
$T->Insert(3,5,new Br());
$T->Insert(3,5,"Here, on the bottom of the image, you can see how the rocky surface is refracted. Below the water surface it seems to be flatter than above.");
$T->aRowClass[5]='q';


$T->Insert(1,6,"3D texturing");
$T->SetClass(1,6,"sublegend");
$T->Insert(2,6,"Usage of heightmap allows to store third coordinate after a hit, which can be used later for texturing. This is being done with landscape texture.
    
Terrain uses a single texture, which checks height coordinate and applies different color based on it. There are main 3 regions - ground, grass and rocks which are slightly melted one with another to avoid ugly straight lines.

Also using 3d texture allowed me to avoid ugly streching artefacts at high gradient regions.");
$T->Insert(3,6,new Link("desc_3dtx.png","Frame 501 - aproaching the candle","_blank"));
$T->Insert(3,6,new Br());
$T->Insert(3,6,"Here all three levels can be easily seen.<br>
    The slightly brighter brown surface at the horison is a background texture and is not a part of heightmap.");
$T->aRowClass[]='q';

$T->Insert(1,7,"Procedural Texturing");
$T->SetClass(1,7,"sublegend");
$T->Insert(2,7,"There are no external textures in the animation,
all are generated at run-time, mostly using different combination of perlin noise. Main two textures is the landscape texture and wood texture.

Wood texture takes a value of perlin noise as an argument for a cosine function. This way I obtain repetitive curves which - when streached - simulate wood quite well.

Landscape 3d texture is more complicated one. The rocks use several layers of perlin noise to generate rocks of different size. The biggest ones are allowed to appear lower, over the grass. Edges of the rocks are blended with whatever they hide to avoid sharp edges.");
$T->Insert(3,7,new Link("desc_woodtex.png","Frame 770 - after a jump","_blank"));
$T->Insert(3,7,new Br());
$T->Insert(3,7,"Viewer is very close to the table here, you can observe the texture in detail, especially in the brightened part at the bottom of the image.");
$T->Insert(3,7,new Br());
$T->Insert(3,7,new Link("desc_rocks.png","Frame 128 - rocky ground","_blank"));
$T->Insert(3,7,new Br());
$T->Insert(3,7,"Rocky region. Observe different sizes of rocks. There is a single component on the second plan on the left, while lots of smaller rocks on the right. You can also see rocks on the grass on the first plan. This is all generated by a single texture object!");
$T->Insert(3,7,new Br());
$T->Insert(3,7,new Link("desc_table.png","Table object","_blank"));
$T->Insert(3,7," (not on the video)");
$T->Insert(3,7,new Br());
$T->Insert(3,7,new Link("desc_main_edge.png","Edges at main image","_blank"));
$T->Insert(3,7,new Br());
$T->Insert(3,7,"Main image with applied edge detection filter to emphasise the pattern of the wooden texture");
$T->aRowClass[7]='q';

$T->Insert(1,8,"Remote texturing");
$T->SetClass(1,8,"sublegend");
$T->Insert(2,8,"The sky texture is not associated with any object, instead it is applied to world itself.<br>
The texture is not shaded just applied directly when ray does not hit any object in the scene. It has to be a 3d texture because it takes ray direction vector as an argument to get a pixel. As a result you see a unit sphere curved out of the texture, seen from the point [0,0,0] which does not depend on camera location.
<br>
This approach guarantees you will never see nothingness. At least you see a brown ground instead of black, green or pink(!) background color.");
$T->Insert(3,8,new Link("desc_3dtx.png","Frame 501 - aproaching the candle","_blank"));
$T->Insert(3,8,new Br());
$T->Insert(3,8,"You can see stars when direction vector goes up and brown surface when it is moving down. Thus horison is exactly where it should be - at the height of camera.");
$T->aRowClass[]='q';


$T->Insert(1,9,"Simplified Integrated Intensity Volume Rendering");
$T->SetClass(1,9,"sublegend");
$T->Insert(2,9,"A very simple integration aproach was applied to render the fire.<br>
It is controled by GlassShader which projects ray inside and behind the object and then blends the background color with the object's color depending linearly on traversal length inside the object. Mathematically it can be seen as integraction over box-like-function scalar field.

The flame is rather opaque but this aproach smooths its edges and it no longer looks like quake1 squarish fire meshes.");
$T->Insert(3,9,new Link("desc_fireblend.png","Frame 1036 - The Candle","_blank"));
$T->Insert(3,9,new Br());
$T->Insert(3,9,"Here if you look closely, you will see how top of the fire object blends with dark edge of a mountain behind. Observe, the edge is less visible in the middle, because flame is wider there and thus less transparent.<br>
Similar effect can be seen on the main image");
$T->aRowClass[]='q';

$T->Insert(1,10,"Cubic B-Spline Camera Path");
$T->SetClass(1,10,"sublegend");
$T->Insert(2,10,"Camera movement is defined by a path route.<br>
Knot points are explicitly provided and a smooth path is generated by joining cubic B-splines. It is guaranteed that camera moves through the knot points.
<br>
A nice webpage on splines was helpful for me to code it:");
$T->Insert(2,10,new Br());
$T->Insert(2,10,new Link("http://www.ibiblio.org/e-notes/Splines/Intro.htm","http://www.ibiblio.org/e-notes/Splines/Intro.htm"));
$T->Insert(3,10,"N/A");
$T->aRowClass[]='q';
/*
$T->Insert(1,,"");
$T->SetClass(1,,"sublegend");
$T->Insert(2,,"");
$T->Insert(3,,new Link("desc_.png","Frame ","_blank"));
$T->Insert(3,,new Br());
$T->Insert(3,,new Link("desc_.png","Frame ","_blank"));
$T->aRowClass[]='q';

$T->Insert(1,,"");
$T->SetClass(1,,"sublegend");
$T->Insert(2,,"");
$T->Insert(3,,"N/A");
$T->aRowClass[]='q';
*/

$H->Insert($T);

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Implementation specific");
$T->SetCols(3);
$T->Join(1,1,3,1);
$T->aRowClass[1]='title';
$T->Insert(1,2,"Element");
$T->Insert(2,2,"Description");
$T->Insert(3,2,"Example (if applicable)");
$T->aRowClass[2]='legend';

$T->Insert(1,3,"Vectors");
$T->SetClass(1,3,"sublegend");
$T->Insert(2,3,"Vector class has been highly edited.<br>
It is now a template, taking dimension as a parameter.
To avoid time loss and increase functionality 2d and 3d class instances
are specifically written.");
$T->Insert(3,3,"N/A");
$T->aRowClass[3]='q';

$T->Insert(1,4,"Matrices");
$T->SetClass(1,4,"sublegend");
$T->Insert(2,4,"Square matrices are also templates, depending on their dimension.<br>
I use only 2d and 3d matrices, some functionality of higher dimension matrices are not implemented (i.e. inversing) because efficient algorithms are not that easy and there would be of no use for the rendering.

Matrices are used to change coordinate system of a HeightMap and optionaly, for entities (see below)");
$T->Insert(3,4,"N/A");
$T->aRowClass[4]='q';

$T->Insert(1,5,"Quaternions");
$T->SetClass(1,5,"sublegend");
$T->Insert(2,5,"Provide basic operation in the quaternion number space, and some useful vector transformation - namely rotation.<br>
Used to rotate entities (see below)");
$T->Insert(3,5,new Link("desc_fall.png","Frame 1062 - Falling candle","_blank"));
$T->Insert(3,5,new Br());
$T->Insert(3,5,"Candle being rotated when falling");
$T->aRowClass[]='q';

$T->Insert(1,6,"BVH");
$T->SetClass(1,6,"sublegend");
$T->Insert(2,6,"In my version of MicroTracer the Object class is a derivative of Primitive. This way one object may be an element of another forming a hierarchy. What is more, one object may be referenced many times. Very simple garbage collector is implemented to free memory of such object only when it is not referenced by anything.<br>
There are also Entities, which are more advanced objects (thus also primitives!). Entity allow transformations - translation, rotation or even (optionally) arbitrary linear transformation. When ray hits the bounding box of entity, it is transformed into local reference system and intersection procedure continues.<br>
Care has been taken to correctly transform back hit time (ray.t), and normals.
<br>
All objects in the video are entities (terrain, table, candle, candle fire). They could be easily tested, modeled etc, and then put together using translations.
<br>
It would be difficult for me to put all primitives into one huge object, it would be against the design of my raytracer. Therefore I am unable to make time comparison between those two approaches.
");
$T->Insert(3,6,new Link("desc_table.png","Table object","_blank"));
$T->Insert(3,6," (not on video)");
$T->Insert(3,6,"<br>");
$T->Insert(3,6,"All table legs is only a signle object in memory, with four
    references by Entities with applied different translation vectors.");
$T->Insert(3,6,new Br());
$T->aRowClass[6]='q';

$T->Insert(1,7,"Volume Heuristic KD-Tree");
$T->SetClass(1,7,"sublegend");
$T->Insert(2,7,'Equivalent of 2D Surface Area Heuristc.<br>
To find minimum of cost function i project bounding boxes of all primitives into one axis, then I convert them into points with flags "beginning" and "end", and then sort them. When it is done, I go through all points and compute the cost function. At each point I know many "begin"-s and "end"-s I encountered so far, so I know how many primitives are on the left and right side of my point. This way I can compute cost function in constant time at each position.
<br>
Algorithm complexity is equal to sorting time. STL sort function has guaranteed O(n log n) complexity including the worst case.
<br><br>
More primitive heuristic, which I denote AVG-SD, computes center of mass of all
primitives (average) and standard deviation in three directions parallel to axis. Split goes through the center, against the axis with highest SD');
$U=new Table();
$U->sClass='block';
$U->Insert(1,1,"Time execution comparison<br>");
$U->aRowClass[1]='title';
$U->Insert(1,1,new Link("desc_fall.png","Frame 1062 - Falling candle","_blank"));
$U->Insert(1,2,'Volume Heuristic');
$U->SetClass(1,2,'legend');
$U->Insert(2,2,'10min 01sec');
$U->Insert(1,3,'AVG-SD Heuristic');
$U->SetClass(1,3,'legend');
$U->Insert(2,3,'48min 02sec');
$U->Insert(1,4,'No KD-Tree');
$U->SetClass(1,4,'legend');
$U->Insert(2,4,'17min 30sec');
$U->Join(1,1,2,1);
$T->Insert(3,7,$U);
$T->Insert(3,7,"This somewhat surprising result can be explained by the fact
    that naive AVG-SD algorithm tends to duplicate primitives a lot. Because
    my primitives are usually not that primitive - those can be heightmaps
    or whole objects lower in hierarhy - such duplication is extremely costly.
    As a result KD-Tree with this heuristic is worse than if there was no KD-Tree at all. In the latter case, optimalisation is held fully by BVH.");
$T->aRowClass[7]='q';
$H->Insert($T);

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Files");
$T->SetCols(2);
$T->Join(1,1,2,1);
$T->aRowClass[1]='title';
$T->Insert(1,2,"File name");
$T->Insert(2,2,"Description");
$T->aRowClass[2]='legend';

$T->Insert(1,3,"box.h");
$T->SetClass(1,3,"sublegend");
$T->Insert(2,3,"Extendable bounding box");
$T->aRowClass[3]='q';

$T->Insert(1,4,"camera.h<br>cameraperspective.h");
$T->SetClass(1,4,"sublegend");
$T->Insert(2,4,"Perspective camera model");
$T->aRowClass[4]='q';

$T->Insert(1,5,"cameramover.h");
$T->SetClass(1,5,"sublegend");
$T->Insert(2,5,"Controls the movement of a camera");
$T->aRowClass[5]='q';

$T->Insert(1,6,"candle.h<br>candle.cpp");
$T->SetClass(1,6,"sublegend");
$T->Insert(2,6,"Creates the candle without flame");
$T->aRowClass[6]='q';

$T->Insert(1,7,"candlefire.h<br>candlefire.cpp");
$T->SetClass(1,7,"sublegend");
$T->Insert(2,7,"Creates flame of the candle");
$T->aRowClass[7]='q';

$T->Insert(1,8,"config.h");
$T->SetClass(1,8,"sublegend");
$T->Insert(2,8,"Main configuration file which controls the scene and algorithms");
$T->aRowClass[8]='q';

$T->Insert(1,9,"debug.h");
$T->SetClass(1,9,"sublegend");
$T->Insert(2,9,"Set of useful macros for debugging");
$T->aRowClass[9]='q';

$T->Insert(1,10,"entity.h");
$T->SetClass(1,10,"sublegend");
$T->Insert(2,10,"Implements the Entity described above. Uses ref.h for transformations");
$T->aRowClass[10]='q';

$T->Insert(1,11,"generator.h<br>generator.cpp");
$T->SetClass(1,11,"sublegend");
$T->Insert(2,11,"A set of useful functions for generating objects in scene, i.e. cones, cut-cones, quads etc.");
$T->aRowClass[11]='q';

$T->Insert(1,12,"heightmap.h<br>heightmap.cpp");
$T->SetClass(1,12,"sublegend");
$T->Insert(2,12,"Implementation of the dismplacement map.");
$T->aRowClass[12]='q';

$T->Insert(1,13,"image.h<br>image.cpp");
$T->SetClass(1,13,"sublegend");
$T->Insert(2,13,"I/O for images");
$T->aRowClass[13]='q';

$T->Insert(1,14,"interpolation.h");
$T->SetClass(1,14,"sublegend");
$T->Insert(2,14,"A set of useful interpolation functions");
$T->aRowClass[14]='q';

$T->Insert(1,15,"interval.h<br>intervalset.h");
$T->SetClass(1,15,"sublegend");
$T->Insert(2,15,"classes used to store primitive extremes in Volume Heristic for kd-tree");
$T->aRowClass[15]='q';

$T->Insert(1,16,"kdtree.h<br>kdtree.cpp");
$T->SetClass(1,16,"sublegend");
$T->Insert(2,16,"Implementation of KD tree");
$T->aRowClass[16]='q';

$T->Insert(1,17,"landscape.h<br>landscape.cpp");
$T->SetClass(1,17,"sublegend");
$T->Insert(2,17,"Creates the terrain and water. Also the 3d landscape texture is here");
$T->aRowClass[17]='q';

$T->Insert(1,18,"light.h");
$T->SetClass(1,18,"sublegend");
$T->Insert(2,18,"Abstract light source");
$T->aRowClass[18]='q';

$T->Insert(1,19,"lightdistant.h");
$T->SetClass(1,19,"sublegend");
$T->Insert(2,19,"A distant light source with no attenuation");
$T->aRowClass[19]='q';

$T->Insert(1,20,"lightpoint.h");
$T->SetClass(1,20,"sublegend");
$T->Insert(2,20,"Simple point source");
$T->aRowClass[20]='q';

$T->Insert(1,21,"lightquadarea.h");
$T->SetClass(1,21,"sublegend");
$T->Insert(2,21,"Quad light source approximated by numerous point lights. Actually it is never used in the rendering");
$T->aRowClass[21]='q';

$T->Insert(1,22,"lightsphere.h");
$T->SetClass(1,22,"sublegend");
$T->Insert(2,22,"A simpler version to Quad Area light. Random point lights are taken from a ball of some radius. No normal vector is defined, light intensity is equal in all direction.");
$T->aRowClass[]='q';

$T->Insert(1,23,"mat.h");
$T->SetClass(1,23,"sublegend");
$T->Insert(2,23,"Implementation of quare matrices");
$T->aRowClass[23]='q';

$T->Insert(1,24,"object.h<br>object.cpp");
$T->SetClass(1,24,"sublegend");
$T->Insert(2,24,"Simplest object which can contain primitives and keeps kd-tree over them.");
$T->aRowClass[]='q';

$T->Insert(1,25,"pngHelper.h");
$T->SetClass(1,25,"sublegend");
$T->Insert(2,25,"Interface to the PNG library");
$T->aRowClass[25]='q';

$T->Insert(1,26,"primitive.h");
$T->SetClass(1,26,"sublegend");
$T->Insert(2,26,"Abstract primitive");
$T->aRowClass[26]='q';

$T->Insert(1,27,"primheighttriangle.h");
$T->SetClass(1,27,"sublegend");
$T->Insert(2,27,"Triangle with applied heightmap");
$T->aRowClass[27]='q';

$T->Insert(1,28,"priminfiniteplane.h");
$T->SetClass(1,28,"sublegend");
$T->Insert(2,28,"Infinite plane. It was used at some version of my program, but it isn't anymore");
$T->aRowClass[28]='q';

$T->Insert(1,29,"primsmoothheighttriangle.h");
$T->SetClass(1,29,"sublegend");
$T->Insert(2,29,"Triangle with applied heightmap and per-vertex base normals, which are later combined with the computed gradient");
$T->aRowClass[]='q';

$T->Insert(1,30,"primsmoothtriangle.h");
$T->SetClass(1,30,"sublegend");
$T->Insert(2,30,"Flat triangle with per-vertex normals");
$T->aRowClass[30]='q';

$T->Insert(1,31,"primtexturedsmoothtriangle.h");
$T->SetClass(1,31,"sublegend");
$T->Insert(2,31,"Textured flat triangle with per-vertex normals");
$T->aRowClass[31]='q';

$T->Insert(1,32,"primtexturedtriangle.h");
$T->SetClass(1,32,"sublegend");
$T->Insert(2,32,"Textured triangle");
$T->aRowClass[32]='q';

$T->Insert(1,33,"primtriangle.h");
$T->SetClass(1,33,"sublegend");
$T->Insert(2,33,"A single triangle with no additional attributes");
$T->aRowClass[33]='q';

$T->Insert(1,34,"quaterion.h");
$T->SetClass(1,34,"sublegend");
$T->Insert(2,34,"Implements quaternions");
$T->aRowClass[34]='q';

$T->Insert(1,35,"rand.h<br>rand.cpp");
$T->SetClass(1,35,"sublegend");
$T->Insert(2,35,"Interface to some simple random functions, including repetitive random functions and Perlin noise");
$T->aRowClass[35]='q';

$T->Insert(1,36,"ray.h");
$T->SetClass(1,36,"sublegend");
$T->Insert(2,36,"Ray object used for raytracing");
$T->aRowClass[36]='q';

$T->Insert(1,37,"ref.h");
$T->SetClass(1,37,"sublegend");
$T->Insert(2,37,"Implements neccessary transformations between two reference systems");
$T->aRowClass[37]='q';

$T->Insert(1,38,"sampler.h");
$T->SetClass(1,38,"sublegend");
$T->Insert(2,38,"Abstract sampler for pixel coloring");
$T->aRowClass[38]='q';

$T->Insert(1,39,"samplerrandom.h<br>samplerregular.h<br>samplerstraitified.h");
$T->SetClass(1,39,"sublegend");
$T->Insert(2,39,"Different kind of samplers. The straitified version is used in the rendering");
$T->aRowClass[]='q';

$T->Insert(1,40,"shader.h");
$T->SetClass(1,40,"sublegend");
$T->Insert(2,40,"Abstract shader");
$T->aRowClass[40]='q';

$T->Insert(1,41,"shadercoud.h");
$T->SetClass(1,41,"sublegend");
$T->Insert(2,41,"Generates coulds. Never used.");
$T->aRowClass[41]='q';

$T->Insert(1,42,"shaderflattransparent.h");
$T->SetClass(1,42,"sublegend");
$T->Insert(2,42,"To be used with textures with alpha channel. Never used.");
$T->aRowClass[42]='q';

$T->Insert(1,43,"shaderglass.h");
$T->SetClass(1,43,"sublegend");
$T->Insert(2,43,"Implements ray traversal through dimm object i.e. dimm glass. Used for candle fire");
$T->aRowClass[43]='q';

$T->Insert(1,44,"shaderphong.h");
$T->SetClass(1,44,"sublegend");
$T->Insert(2,44,"Implements simple illumination function for given point.");
$T->aRowClass[44]='q';

$T->Insert(1,45,"shadersimple.h");
$T->SetClass(1,45,"sublegend");
$T->Insert(2,45,"Simply takes the color of given object or texture texel and returns it. No lightning etc. Used mainly for debugging");
$T->aRowClass[]='q';

$T->Insert(1,46,"shaderwater.h");
$T->SetClass(1,46,"sublegend");
$T->Insert(2,46,"Reflets and refracts the ray and later blends them");
$T->aRowClass[46]='q';

$T->Insert(1,47,"table.h<br>table.cpp");
$T->SetClass(1,47,"sublegend");
$T->Insert(2,47,"Creates the table on which the candle is standing");
$T->aRowClass[47]='q';

$T->Insert(1,48,"texture.h");
$T->SetClass(1,48,"sublegend");
$T->Insert(2,48,"Abstract texture class with defined 2 and 3 argument getTexel function");
$T->aRowClass[48]='q';

$T->Insert(1,49,"textureperlin.h<br>texture3dperlin.h");
$T->SetClass(1,49,"sublegend");
$T->Insert(2,49,"Parametisable texture using 2D/3D perlin noise");
$T->aRowClass[]='q';

$T->Insert(1,50,"texturestars.h");
$T->SetClass(1,50,"sublegend");
$T->Insert(2,50,"Implement stars and ground for the world");
$T->aRowClass[50]='q';

$T->Insert(1,51,"texturewood.h");
$T->SetClass(1,51,"sublegend");
$T->Insert(2,51,"Imitates wood using perlin noise");
$T->aRowClass[51]='q';

$T->Insert(1,52,"utils.h");
$T->SetClass(1,52,"sublegend");
$T->Insert(2,52,"A bunch of useful functions");
$T->aRowClass[52]='q';

$T->Insert(1,53,"vec.h<br>vec2f.h<br>vec3f.h");
$T->SetClass(1,53,"sublegend");
$T->Insert(2,53,"Implements the vector template and two specialistions");
$T->aRowClass[53]='q';

$T->Insert(1,54,"world.h");
$T->SetClass(1,54,"sublegend");
$T->Insert(2,54,"This holds the whole scene");
$T->aRowClass[54]='q';

/*
$T->Insert(1,,"");
$T->SetClass(1,,"sublegend");
$T->Insert(2,,"");
$T->aRowClass[]='q';

*/
$H->Insert($T);

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Trivia");
$T->SetCols(2);
$T->Join(1,1,2,1);
$T->aRowClass[1]='title';

$T->Insert(1,2,"Video length");
$T->SetClass(1,2,"sublegend");
$T->Insert(2,2,"55 seconds");
$T->aRowClass[2]='q';

$T->Insert(1,3,"Number of frames");
$T->SetClass(1,3,"sublegend");
$T->Insert(2,3,"1320");
$T->aRowClass[3]='q';

$T->Insert(1,4,"Render time");
$T->SetClass(1,4,"sublegend");
$T->Insert(2,4,"approx. 2 days");
$T->aRowClass[4]='q';

$T->Insert(1,5,"Frame longest render time");
$T->SetClass(1,5,"sublegend");
$T->Insert(2,5,"3 hours");
$T->aRowClass[5]='q';

$T->Insert(1,6,"Frame shortest render time");
$T->SetClass(1,6,"sublegend");
$T->Insert(2,6,"4 seconds");
$T->aRowClass[6]='q';

$T->Insert(1,7,"Number of rays per pixel");
$T->SetClass(1,7,"sublegend");
$T->Insert(2,7,"4");
$T->aRowClass[7]='q';

$T->Insert(1,8,"Number of light sources");
$T->SetClass(1,8,"sublegend");
$T->Insert(2,8,"70");
$T->aRowClass[8]='q';

$T->Insert(1,9,"Number of explicit triangles");
$T->SetClass(1,9,"sublegend");
$T->Insert(2,9,"approx. 640<br>-terrain: 2<br>-water: 6<br>-candle: 101<br>-table: 22<br>-fire: approx 509");
$T->aRowClass[9]='q';

$T->Insert(1,10,"Number of all triangles<br>including heightmap virtual triangles");
$T->SetClass(1,10,"sublegend");
$T->Insert(2,10,"approx. 210000<br>-terrain: 32768<br>-water: 119000<br>-candle: 8261<br>-table: 51221<br>-fire: approx 509)");
$T->aRowClass[10]='q';

$T->Insert(1,11,"Memory consuption");
$T->SetClass(1,11,"sublegend");
$T->Insert(2,11,"9 megabytes");
$T->aRowClass[11]='q';

$T->Insert(1,12,"Source code size");
$T->SetClass(1,12,"sublegend");
$T->Insert(2,12,"approx. 10000 lines<br>approx. 250 kilobytes");
$T->aRowClass[12]='q';

$H->Insert($T);

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Rendering location");
$T->SetCols(2);
$T->Join(1,1,2,1);
$T->aRowClass[1]='title';
$T->Insert(1,2,"Frames");
$T->Insert(2,2,"Computer");
$T->aRowClass[2]='legend';

$T->Insert(1,3,"0-350");
$T->SetClass(1,3,"sublegend");
$T->Insert(2,3,"My laptop");
$T->aRowClass[3]='q';

$T->Insert(1,4,"351-599");
$T->SetClass(1,4,"sublegend");
$T->Insert(2,4,"Maciek's mac");
$T->aRowClass[4]='q';

$T->Insert(1,5,"600-609");
$T->SetClass(1,5,"sublegend");
$T->Insert(2,5,"virgo.ii.uj.edu.pl");
$T->aRowClass[5]='q';

$T->Insert(1,6,"610");
$T->SetClass(1,6,"sublegend");
$T->Insert(2,6,"Maciek's mac");
$T->aRowClass[6]='q';

$T->Insert(1,7,"611-614");
$T->SetClass(1,7,"sublegend");
$T->Insert(2,7,"virgo.ii.uj.edu.pl");
$T->aRowClass[7]='q';

$T->Insert(1,8,"615-619");
$T->SetClass(1,8,"sublegend");
$T->Insert(2,8,"Maciek's mac");
$T->aRowClass[8]='q';

$T->Insert(1,9,"620-646");
$T->SetClass(1,9,"sublegend");
$T->Insert(2,9,"virgo.ii.uj.edu.pl");
$T->aRowClass[9]='q';

$T->Insert(1,10,"647-764");
$T->SetClass(1,10,"sublegend");
$T->Insert(2,10,"Maciek's mac");
$T->aRowClass[10]='q';

$T->Insert(1,11,"765-784");
$T->SetClass(1,11,"sublegend");
$T->Insert(2,11,"virgo.ii.uj.edu.pl");
$T->aRowClass[11]='q';

$T->Insert(1,12,"785");
$T->SetClass(1,12,"sublegend");
$T->Insert(2,12,"My laptop");
$T->aRowClass[12]='q';

$T->Insert(1,13,"786-809");
$T->SetClass(1,13,"sublegend");
$T->Insert(2,13,"Maciek's mac");
$T->aRowClass[13]='q';

$T->Insert(1,14,"810-884");
$T->SetClass(1,14,"sublegend");
$T->Insert(2,14,"virgo.ii.uj.edu.pl");
$T->aRowClass[14]='q';

$T->Insert(1,15,"885");
$T->SetClass(1,15,"sublegend");
$T->Insert(2,15,"tsk");
$T->aRowClass[15]='q';

$T->Insert(1,16,"886-1320");
$T->SetClass(1,16,"sublegend");
$T->Insert(2,16,"Maciek's mac");
$T->aRowClass[16]='q';

$H->Insert($T);

$H->Insert("Many thanks to my friend Maciek for his hardware aid, patience and comments");
$H->Br();
$H->Br();
$H->Br();
$H->Draw();
?>
